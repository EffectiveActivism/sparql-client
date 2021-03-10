<?php declare(strict_types = 1);

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Constant;
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
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SparQlClient implements SparQlClientInterface
{
    use CacheTrait;

    protected TagAwareCacheInterface $cacheAdapter;

    protected array $extraNamespaces = [];

    protected HttpClientInterface $httpClient;

    protected LoggerInterface $logger;

    protected array $namespaces = [];

    protected SerializerInterface $serializer;

    protected string $sparQlEndpoint;

    public function __construct(array $configuration, TagAwareCacheInterface $cacheAdapter, HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->sparQlEndpoint = $configuration['sparql_endpoint'];
        $this->cacheAdapter = $cacheAdapter;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->namespaces = $configuration['namespaces'];
        $normalizers = [new SparQlResultDenormalizer()];
        $encoders = [new XmlEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @throws SparQlException
     */
    public function execute(StatementInterface $statement, bool $toTriples = false): array
    {
        return match (get_class($statement)) {
            DeleteStatement::class => $this->handleDeleteStatement($statement),
            InsertStatement::class => $this->handleInsertStatement($statement),
            ReplaceStatement::class => $this->handleReplaceStatement($statement),
            SelectStatement::class => $this->handleSelectStatement($statement, $toTriples)
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
                $cacheItem = $this->cacheAdapter->getItem($queryKey);
                $cacheItem->set($responseContent);
                $cacheItem->tag($tags);
                $this->cacheAdapter->save($cacheItem);
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
    protected function handleDeleteStatement(DeleteStatementInterface $statement): array
    {
        $query = $statement->toQuery();
        $parameters = ['body' => ['update' => $query]];
        try {
            $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters)->getContent(false);
            // Invalidate cache for delete and update statements.
            $tags = [];
            /** @var TripleInterface $triple */
            foreach (array_merge([$statement->getTripleToDelete()], $statement->getConditions(), $statement->getOptionalConditions()) as $triple) {
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
    protected function handleReplaceStatement(ReplaceStatementInterface $statement): array
    {
        $query = $statement->toQuery();
        $parameters = ['body' => ['update' => $query]];
        try {
            $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters)->getContent(false);
            // Invalidate cache for delete and update statements.
            $tags = [];
            /** @var TripleInterface $triple */
            foreach (array_merge([$statement->getOriginal(), $statement->getReplacement()], $statement->getConditions(), $statement->getOptionalConditions()) as $triple) {
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
        return new DeleteStatement($triple, $this->getNamespaces());
    }

    public function insert(TripleInterface $triple): InsertStatement
    {
        return new InsertStatement($triple, $this->getNamespaces());
    }

    public function replace(TripleInterface $triple): ReplaceStatementInterface
    {
        return new ReplaceStatement($triple, $this->getNamespaces());
    }

    public function select(array $variables): SelectStatement
    {
        return new SelectStatement($variables, $this->getNamespaces());
    }

    /**
     * Getters.
     */

    public function getNamespaces(): array
    {
        return array_merge(Constant::W3C_NAMESPACES, $this->namespaces, $this->extraNamespaces);
    }

    /**
     * Setters.
     */

    public function setExtraNamespaces(array $extraNamespaces): SparQlClientInterface
    {
        $this->extraNamespaces = $extraNamespaces;
        return $this;
    }
}
