<?php declare(strict_types = 1);

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlAskDenormalizer;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\AskStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\AskStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\StatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatement;
use EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlResultDenormalizer;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
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
        $normalizers = [
            new SparQlResultDenormalizer(),
            new SparQlAskDenormalizer(),
        ];
        $encoders = [new XmlEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @throws SparQlException
     */
    public function execute(StatementInterface $statement, bool $toTriples = false): array|bool
    {
        return match (get_class($statement)) {
            AskStatement::class => $this->handleAskStatement($statement),
            ConstructStatement::class => $this->handleQueryStatment($statement, $toTriples),
            DeleteStatement::class => $this->handleDeleteStatement($statement),
            InsertStatement::class => $this->handleInsertStatement($statement),
            ReplaceStatement::class => $this->handleReplaceStatement($statement),
            SelectStatement::class => $this->handleQueryStatment($statement, $toTriples)
        };
    }

    /**
     * @throws SparQlException
     */
    protected function handleQueryStatment(ConstructStatementInterface|SelectStatementInterface $statement, bool $toTriples): array
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['query' => $query]];
        $cacheHit = true;
        $responseContent = null;
        $queryKey = $this->getKey($query);
        try {
            $responseContent = $this->cacheAdapter->get($queryKey, function (ItemInterface $item) use ($parameters, &$cacheHit) {
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
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception);
        }
        // Update cache for successful select statement requests, if uncached.
        if (!$cacheHit) {
            $tags = $this->extractTags($statement->getConditions());
            try {
                $cacheItem = $this->cacheAdapter->getItem($queryKey);
                $cacheItem->set($responseContent);
                $cacheItem->tag($tags);
                $this->cacheAdapter->save($cacheItem);
            } catch (CacheException|InvalidArgumentException $exception) {
                throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }
        // Return response as either a set of terms or a set of triples.
        $result = $sets = $this->serializer->deserialize($responseContent, SparQlResultDenormalizer::TYPE, 'xml');
        if ($toTriples === true) {
            $conditions = $statement->getConditions();
            foreach ($sets as $set) {
                /** @var TripleInterface $condition */
                foreach ($conditions as &$condition) {
                    if ($condition instanceof TripleInterface) {
                        /** @var TermInterface $term */
                        foreach ($set as $term) {
                            if (get_class($condition->getSubject()) === Variable::class && $condition->getSubject()->getVariableName() === $term->getVariableName()) {
                                $condition->setSubject($term);
                            }
                            if (get_class($condition->getPredicate()) === Variable::class && $condition->getPredicate()->getVariableName() === $term->getVariableName()) {
                                $condition->setPredicate($term);
                            }
                            if (get_class($condition->getObject()) === Variable::class && $condition->getObject()->getVariableName() === $term->getVariableName()) {
                                $condition->setObject($term);
                            }
                        }
                    }
                }
            }
            $result = $conditions;
        }
        return $result;
    }

    /**
     * @throws SparQlException
     */
    protected function handleAskStatement(AskStatementInterface $statement): bool
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['query' => $query]];
        $cacheHit = true;
        $responseContent = null;
        $queryKey = $this->getKey($query);
        try {
            $responseContent = $this->cacheAdapter->get($queryKey, function (ItemInterface $item) use ($parameters, &$cacheHit) {
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
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception);
        }
        // Update cache for successful ask statement requests, if uncached.
        if (!$cacheHit) {
            $tags = $this->extractTags($statement->getConditions());
            try {
                $cacheItem = $this->cacheAdapter->getItem($queryKey);
                $cacheItem->set($responseContent);
                $cacheItem->tag($tags);
                $this->cacheAdapter->save($cacheItem);
            } catch (CacheException|InvalidArgumentException $exception) {
                throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }
        return $this->serializer->deserialize($responseContent, SparQlAskDenormalizer::TYPE, 'xml');
    }

    /**
     * @throws SparQlException
     */
    protected function handleDeleteStatement(DeleteStatementInterface $statement): array
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['update' => $query]];
        try {
            $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters)->getContent();
            // Invalidate cache for delete statements.
            $tags = $this->extractTags(array_merge($statement->getTriplesToDelete(), $statement->getConditions()));
            $this->cacheAdapter->invalidateTags($tags);
        } catch (HttpClientExceptionInterface|InvalidArgumentException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception);
        }
        return [];
    }

    /**
     * @throws SparQlException
     */
    protected function handleInsertStatement(InsertStatementInterface $statement): array
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['update' => $query]];
        try {
            $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters)->getContent();
            // Invalidate cache for insert statements.
            $tags = $this->extractTags(array_merge($statement->getTriplesToInsert(), $statement->getConditions()));
            $this->cacheAdapter->invalidateTags($tags);
        } catch (HttpClientExceptionInterface|InvalidArgumentException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception);
        }
        return [];
    }

    /**
     * @throws SparQlException
     */
    protected function handleReplaceStatement(ReplaceStatementInterface $statement): array
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['update' => $query]];
        try {
            $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters)->getContent();
            // Invalidate cache for delete and update statements.
            $tags = $this->extractTags(array_merge($statement->getOriginals(), $statement->getReplacements(), $statement->getConditions()));
            $this->cacheAdapter->invalidateTags($tags);
        } catch (HttpClientExceptionInterface|InvalidArgumentException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception);
        }
        return [];
    }

    /**
     * @throws SparQlException
     */
    public function ask(): AskStatementInterface
    {
        return new AskStatement($this->getNamespaces());
    }

    /**
     * @throws SparQlException
     */
    public function construct(array $triples): ConstructStatementInterface
    {
        return new ConstructStatement($triples, $this->getNamespaces());
    }

    /**
     * @throws SparQlException
     */
    public function delete(array $triples): DeleteStatement
    {
        return new DeleteStatement($triples, $this->getNamespaces());
    }

    /**
     * @throws SparQlException
     */
    public function insert(array $triples): InsertStatement
    {
        return new InsertStatement($triples, $this->getNamespaces());
    }

    /**
     * @throws SparQlException
     */
    public function replace(array $triples): ReplaceStatementInterface
    {
        return new ReplaceStatement($triples, $this->getNamespaces());
    }

    /**
     * @throws SparQlException
     */
    public function select(array $variables): SelectStatement
    {
        return new SelectStatement($variables, $this->getNamespaces());
    }

    /**
     * @throws SparQlException
     */
    public function upload(File $file, string $contentType = 'application/x-turtle'): bool
    {
        try {
            $this->httpClient->request('POST', $this->sparQlEndpoint, [
                'headers' => [
                    'Content-Type' => $contentType,
                ],
                'body' => $file->getContent(),
            ]);
            return true;
        } catch (ExceptionInterface $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception);
        }
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
