<?php declare(strict_types = 1);

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Exception\InvalidResultException;
use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Result\AskResult;
use EffectiveActivism\SparQlClient\Result\ConstructResult;
use EffectiveActivism\SparQlClient\Result\DescribeResult;
use EffectiveActivism\SparQlClient\Result\SelectResult;
use EffectiveActivism\SparQlClient\Result\StatementResultInterface;
use EffectiveActivism\SparQlClient\Result\UpdateResult;
use EffectiveActivism\SparQlClient\Result\UpdateResultInterface;
use EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlAskDenormalizer;
use EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlConstructDenormalizer;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\AskStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\AskStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ClearStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\ClearStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\CreateStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\CreateStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\DescribeStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\DescribeStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\DropStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\DropStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\StatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatement;
use EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlResultDenormalizer;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SparQlClient implements SparQlClientInterface
{
    use CacheTrait;

    protected TagAwareCacheInterface $cacheAdapter;

    protected HttpClientInterface $httpClient;

    protected LoggerInterface $logger;

    protected SerializerInterface $serializer;

    protected string $sparQlEndpoint;

    public function __construct(array $configuration, TagAwareCacheInterface $cacheAdapter, HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->sparQlEndpoint = $configuration['sparql_endpoint'];
        $this->cacheAdapter = $cacheAdapter;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $normalizers = [
            new SparQlAskDenormalizer(),
            new SparQlConstructDenormalizer(),
            new SparQlResultDenormalizer(),
        ];
        $encoders = [new XmlEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @throws SparQlException
     */
    public function execute(StatementInterface $statement): StatementResultInterface
    {
        return match (true) {
            $statement instanceof AskStatementInterface => $this->handleAskStatement($statement),
            $statement instanceof ClearStatementInterface => $this->handleClearStatement($statement),
            $statement instanceof ConstructStatementInterface => $this->handleConstructStatement($statement),
            $statement instanceof CreateStatementInterface => $this->handleCreateStatement($statement),
            $statement instanceof DeleteStatementInterface => $this->handleDeleteStatement($statement),
            $statement instanceof DescribeStatementInterface => $this->handleDescribeStatement($statement),
            $statement instanceof DropStatementInterface => $this->handleDropStatement($statement),
            $statement instanceof InsertStatementInterface => $this->handleInsertStatement($statement),
            $statement instanceof ReplaceStatementInterface => $this->handleReplaceStatement($statement),
            $statement instanceof SelectStatementInterface => $this->handleSelectStatement($statement),
            default => throw new SparQlException(sprintf('Unsupported statement type: %s', get_class($statement))),
        };
    }

    /**
     * @throws SparQlException
     */
    protected function handleSelectStatement(SelectStatementInterface $statement): SelectResult
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['query' => $query]];
        $queryKey = $this->getKey($query);
        $rows = null;
        try {
            $responseContent = $this->cacheAdapter->get($queryKey, function (ItemInterface $item) use ($parameters, $query, $statement, &$rows) {
                try {
                    $response = $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters);
                    $responseContent = $response->getContent();
                } catch (HttpClientExceptionInterface $exception) {
                    throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, $this->resolveStatusCode($response ?? null), $this->resolveBody($response ?? null), $query);
                }
                $rows = $this->serializer->deserialize($responseContent, SparQlResultDenormalizer::TYPE, 'xml');
                $tags = $this->extractTags($statement->getConditions());
                foreach ($rows as $row) {
                    $tags = $this->extractTags($row, $tags);
                }
                $item->tag($tags);
                return $responseContent;
            });
        } catch (InvalidArgumentException|\LogicException|InvalidResultException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
        try {
            $rows = $rows ?? $this->serializer->deserialize($responseContent, SparQlResultDenormalizer::TYPE, 'xml');
        } catch (InvalidResultException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
        return new SelectResult($rows);
    }

    /**
     * @throws SparQlException
     */
    protected function handleAskStatement(AskStatementInterface $statement): AskResult
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['query' => $query]];
        $queryKey = $this->getKey($query);
        try {
            $responseContent = $this->cacheAdapter->get($queryKey, function (ItemInterface $item) use ($parameters, $query, $statement) {
                try {
                    $response = $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters);
                    $responseContent = $response->getContent();
                } catch (HttpClientExceptionInterface $exception) {
                    throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, $this->resolveStatusCode($response ?? null), $this->resolveBody($response ?? null), $query);
                }
                $item->tag($this->extractTags($statement->getConditions()));
                return $responseContent;
            });
        } catch (InvalidArgumentException|\LogicException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
        try {
            return new AskResult($this->serializer->deserialize($responseContent, SparQlAskDenormalizer::TYPE, 'xml'));
        } catch (InvalidResultException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
    }

    /**
     * @throws SparQlException
     */
    protected function handleClearStatement(ClearStatementInterface $statement): UpdateResultInterface
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['update' => $query]];
        try {
            $response = $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters);
            $body = $response->getContent();
            $statusCode = $response->getStatusCode();
        } catch (HttpClientExceptionInterface $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, $this->resolveStatusCode($response ?? null), $this->resolveBody($response ?? null), $query);
        }
        try {
            $this->cacheAdapter->invalidateTags($this->extractTags([$statement->getGraph()]));
        } catch (InvalidArgumentException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
        return new UpdateResult($statusCode, $body);
    }

    /**
     * @throws SparQlException
     */
    protected function handleConstructStatement(ConstructStatementInterface $statement): ConstructResult
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['query' => $query]];
        $queryKey = $this->getKey($query);
        $tripleArrays = null;
        try {
            $responseContent = $this->cacheAdapter->get($queryKey, function (ItemInterface $item) use ($parameters, $query, $statement, &$tripleArrays) {
                try {
                    $response = $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters);
                    $responseContent = $response->getContent();
                } catch (HttpClientExceptionInterface $exception) {
                    throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, $this->resolveStatusCode($response ?? null), $this->resolveBody($response ?? null), $query);
                }
                $tripleArrays = $this->serializer->deserialize($responseContent, SparQlConstructDenormalizer::TYPE, 'xml');
                $tags = $this->extractTags($statement->getConditions());
                foreach ($tripleArrays as $tripleArray) {
                    $tags = $this->extractTags($tripleArray, $tags);
                }
                $item->tag($tags);
                return $responseContent;
            });
        } catch (InvalidArgumentException|\LogicException|InvalidResultException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
        try {
            $tripleArrays = $tripleArrays ?? $this->serializer->deserialize($responseContent, SparQlConstructDenormalizer::TYPE, 'xml');
        } catch (InvalidResultException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
        return new ConstructResult(array_map(fn(array $set) => new Triple($set[0], $set[1], $set[2]), $tripleArrays));
    }

    /**
     * @throws SparQlException
     */
    protected function handleDeleteStatement(DeleteStatementInterface $statement): UpdateResultInterface
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['update' => $query]];
        try {
            $response = $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters);
            $body = $response->getContent();
            $statusCode = $response->getStatusCode();
        } catch (HttpClientExceptionInterface $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, $this->resolveStatusCode($response ?? null), $this->resolveBody($response ?? null), $query);
        }
        try {
            $tags = $this->extractTags(array_merge($statement->getTriplesToDelete(), $statement->getConditions()));
            $this->cacheAdapter->invalidateTags($tags);
        } catch (InvalidArgumentException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
        return new UpdateResult($statusCode, $body);
    }

    /**
     * @throws SparQlException
     */
    protected function handleCreateStatement(CreateStatementInterface $statement): UpdateResultInterface
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['update' => $query]];
        try {
            $response = $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters);
            $body = $response->getContent();
            $statusCode = $response->getStatusCode();
        } catch (HttpClientExceptionInterface $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, $this->resolveStatusCode($response ?? null), $this->resolveBody($response ?? null), $query);
        }
        return new UpdateResult($statusCode, $body);
    }

    /**
     * @throws SparQlException
     */
    protected function handleDescribeStatement(DescribeStatementInterface $statement): DescribeResult
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['query' => $query]];
        $queryKey = $this->getKey($query);
        $tripleArrays = null;
        try {
            $responseContent = $this->cacheAdapter->get($queryKey, function (ItemInterface $item) use ($parameters, $query, $statement, &$tripleArrays) {
                try {
                    $response = $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters);
                    $responseContent = $response->getContent();
                } catch (HttpClientExceptionInterface $exception) {
                    throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, $this->resolveStatusCode($response ?? null), $this->resolveBody($response ?? null), $query);
                }
                $tripleArrays = $this->serializer->deserialize($responseContent, SparQlConstructDenormalizer::TYPE, 'xml');
                $tags = $this->extractTags(array_merge($statement->getResources(), $statement->getConditions()));
                foreach ($tripleArrays as $tripleArray) {
                    $tags = $this->extractTags($tripleArray, $tags);
                }
                $item->tag($tags);
                return $responseContent;
            });
        } catch (InvalidArgumentException|\LogicException|InvalidResultException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
        try {
            $tripleArrays = $tripleArrays ?? $this->serializer->deserialize($responseContent, SparQlConstructDenormalizer::TYPE, 'xml');
        } catch (InvalidResultException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
        return new DescribeResult(array_map(fn(array $set) => new Triple($set[0], $set[1], $set[2]), $tripleArrays));
    }

    /**
     * @throws SparQlException
     */
    protected function handleDropStatement(DropStatementInterface $statement): UpdateResultInterface
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['update' => $query]];
        try {
            $response = $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters);
            $body = $response->getContent();
            $statusCode = $response->getStatusCode();
        } catch (HttpClientExceptionInterface $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, $this->resolveStatusCode($response ?? null), $this->resolveBody($response ?? null), $query);
        }
        try {
            $this->cacheAdapter->invalidateTags($this->extractTags([$statement->getGraph()]));
        } catch (InvalidArgumentException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
        return new UpdateResult($statusCode, $body);
    }

    /**
     * @throws SparQlException
     */
    protected function handleInsertStatement(InsertStatementInterface $statement): UpdateResultInterface
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['update' => $query]];
        try {
            $response = $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters);
            $body = $response->getContent();
            $statusCode = $response->getStatusCode();
        } catch (HttpClientExceptionInterface $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, $this->resolveStatusCode($response ?? null), $this->resolveBody($response ?? null), $query);
        }
        try {
            $tags = $this->extractTags(array_merge($statement->getTriplesToInsert(), $statement->getConditions()));
            $this->cacheAdapter->invalidateTags($tags);
        } catch (InvalidArgumentException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
        return new UpdateResult($statusCode, $body);
    }

    /**
     * @throws SparQlException
     */
    protected function handleReplaceStatement(ReplaceStatementInterface $statement): UpdateResultInterface
    {
        $query = $statement->toQuery();
        $this->logger->debug($query);
        $parameters = ['body' => ['update' => $query]];
        try {
            $response = $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters);
            $body = $response->getContent();
            $statusCode = $response->getStatusCode();
        } catch (HttpClientExceptionInterface $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, $this->resolveStatusCode($response ?? null), $this->resolveBody($response ?? null), $query);
        }
        try {
            $extra = $statement->getScopeGraph() !== null ? [$statement->getScopeGraph()] : [];
            $tags = $this->extractTags(array_merge($statement->getOriginals(), $statement->getReplacements(), $statement->getConditions(), $extra));
            $this->cacheAdapter->invalidateTags($tags);
        } catch (InvalidArgumentException $exception) {
            throw new SparQlException($exception->getMessage(), $exception->getCode(), $exception, null, null, $query);
        }
        return new UpdateResult($statusCode, $body);
    }

    /**
     * @throws SparQlException
     */
    public function ask(): AskStatementInterface
    {
        return new AskStatement();
    }

    public function clearGraph(AbstractIri $graph): ClearStatementInterface
    {
        return new ClearStatement($graph);
    }

    /**
     * @throws SparQlException
     */
    public function construct(array $triples): ConstructStatementInterface
    {
        return new ConstructStatement($triples);
    }

    public function createGraph(AbstractIri $graph): CreateStatementInterface
    {
        return new CreateStatement($graph);
    }

    /**
     * @throws SparQlException
     */
    public function delete(array $triples): DeleteStatement
    {
        return new DeleteStatement($triples);
    }

    public function dropGraph(AbstractIri $graph): DropStatementInterface
    {
        return new DropStatement($graph);
    }

    /**
     * @throws SparQlException
     */
    public function describe(array $resources): DescribeStatement
    {
        return new DescribeStatement($resources);
    }

    /**
     * @throws SparQlException
     */
    public function insert(array $triples): InsertStatement
    {
        return new InsertStatement($triples);
    }

    /**
     * @throws SparQlException
     */
    public function replace(array $triples): ReplaceStatementInterface
    {
        return new ReplaceStatement($triples);
    }

    /**
     * @throws SparQlException
     */
    public function select(array $variables): SelectStatement
    {
        return new SelectStatement($variables);
    }

    private function resolveStatusCode(?ResponseInterface $response): ?int
    {
        if ($response === null) {
            return null;
        }
        try {
            return $response->getStatusCode();
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveBody(?ResponseInterface $response): ?string
    {
        if ($response === null) {
            return null;
        }
        try {
            return $response->getContent(false);
        } catch (\Throwable) {
            return null;
        }
    }

}
