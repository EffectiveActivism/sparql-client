<?php declare(strict_types = 1);

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\StatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatement;
use EffectiveActivism\SparQlClient\Serializer\Encoder\TrigEncoder;
use EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlResultDenormalizer;
use EffectiveActivism\SparQlClient\Syntax\Term\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SparQlClient implements SparQlClientInterface
{
    use CacheTrait;

    protected TagAwareCacheInterface $cacheAdapter;

    protected HttpClientInterface $httpClient;

    protected LoggerInterface $logger;

    protected SerializerInterface $serializer;

    protected string $sparQlEndpoint;

    public function __construct(string $sparQlEndpoint, TagAwareCacheInterface $cacheAdapter, HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->sparQlEndpoint = $sparQlEndpoint;
        $this->cacheAdapter = $cacheAdapter;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $normalizers = [new SparQlResultDenormalizer()];
        $encoders = [new TrigEncoder(), new XmlEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @throws SparQlException
     */
    public function execute(StatementInterface $statement, bool $toTriples = false): array
    {
        return match (get_class($statement)) {
            SelectStatement::class => $this->handleSelectStatement($statement, $toTriples),
            DeleteStatement::class, ReplaceStatement::class => $this->handleDeleteAndReplaceStatement($statement),
            InsertStatement::class => $this->handleInsertStatement($statement),
        };
    }

    /**
     * @throws SparQlException
     */
    protected function handleSelectStatement(SelectStatementInterface $statement, bool $toTriples): array
    {
        $query = $statement->toQuery();
        $parameters = ['body' => ['query' => $query]];
        $cacheHit = true;
        $responseContent = null;
        try {
            $responseContent = $this->cacheAdapter->get($this->getKey($query), function (ItemInterface $item) use ($parameters, &$cacheHit) {
                $responseContent = null;
                try {
                    $responseContent = $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters)->getContent();
                    $cacheHit = false;
                } catch (HttpClientExceptionInterface $exception) {
                    throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception);
                }
                return $responseContent;
            });
        } catch (InvalidArgumentException $exception) {
            $this->logger->info($exception->getMessage());
        }
        // Update cache for successful select statement requests, if uncached.
        if (!$cacheHit) {
            $tags = [];
            /** @var TripleInterface $triple */
            foreach (array_merge($statement->getConditions(), $statement->getOptionalConditions()) as $triple) {
                /** @var TermInterface $term */
                foreach ([$triple->getSubject(), $triple->getObject()] as $term) {
                    if ($term instanceof AbstractIri) {
                        $tags[] = $this->getKey($term->serialize());
                    }
                }
            }
            $queryKey = $this->getKey($query);
            try {
                $this->cacheAdapter->getItem($queryKey)->set($responseContent);
                $this->cacheAdapter->getItem($queryKey)->tag($tags);
            } catch (CacheException $exception) {
                $this->logger->info($exception->getMessage());
            }
        }
        // Return response as either a set of terms or a set of triples.
        $result = $sets = $this->serializer->deserialize($responseContent, SparQlResultDenormalizer::TYPE, 'xml');
        if ($toTriples === true) {
            $triples = array_merge($statement->getConditions(), $statement->getOptionalConditions());
            foreach ($sets as $set) {
                /** @var TripleInterface $triple */
                foreach ($triples as $triple) {
                    /** @var TermInterface $term */
                    foreach ($set as $term) {
                        if (get_class($triple->getSubject()) === Variable::class && $triple->getSubject()->getVariableName() === $term->getVariableName()) {
                            $triple->setSubject($term);
                        }
                        if (get_class($triple->getPredicate()) === Variable::class && $triple->getPredicate()->getVariableName() === $term->getVariableName()) {
                            $triple->setPredicate($term);
                        }
                        if (get_class($triple->getObject()) === Variable::class && $triple->getObject()->getVariableName() === $term->getVariableName()) {
                            $triple->setObject($term);
                        }
                    }
                }
            }
            $result = $triples;
        }
        return $result;
    }

    /**
     * @throws SparQlException
     */
    protected function handleDeleteAndReplaceStatement(DeleteStatementInterface|ReplaceStatementInterface $statement): array
    {
        $query = $statement->toQuery();
        $parameters = ['body' => ['update' => $query]];
        try {
            $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters)->getContent(false);
            // Invalidate cache for delete and update statements.
            $tags = [];
            /** @var TripleInterface $triple */
            foreach (array_merge($statement->getConditions(), $statement->getOptionalConditions()) as $triple) {
                /** @var TermInterface $term */
                foreach ([$triple->getSubject(), $triple->getObject()] as $term) {
                    if ($term instanceof AbstractIri) {
                        $tags[] = $this->getKey($term->serialize());
                    }
                }
            }
            $this->cacheAdapter->invalidateTags($tags);
        } catch (HttpClientExceptionInterface $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (InvalidArgumentException $exception) {
            $this->logger->info($exception->getMessage());
        }
        return [];
    }

    /**
     * @throws SparQlException
     */
    protected function handleInsertStatement(InsertStatementInterface $statement): array
    {
        $query = $statement->toQuery();
        $parameters = ['body' => ['update' => $query]];
        try {
            $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters)->getContent(false);
        } catch (HttpClientExceptionInterface $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception);
        }
        return [];
    }

    public function delete(TripleInterface $triple): DeleteStatement
    {
        return new DeleteStatement($triple);
    }

    public function insert(TripleInterface $triple): InsertStatement
    {
        return new InsertStatement($triple);
    }

    public function replace(TripleInterface $triple): ReplaceStatementInterface
    {
        return new ReplaceStatement($triple);
    }

    public function select(array $variables): SelectStatement
    {
        return new SelectStatement($variables);
    }
}
