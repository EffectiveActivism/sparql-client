<?php declare(strict_types = 1);

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\StatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\UpdateStatement;
use EffectiveActivism\SparQlClient\Serializer\Encoder\TrigEncoder;
use EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlResultDenormalizer;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SparQlClient implements SparQlClientInterface
{
    use CacheTrait;

    protected AdapterInterface $cacheAdapter;

    protected HttpClientInterface $httpClient;

    protected LoggerInterface $logger;

    protected SerializerInterface $serializer;

    protected string $sparQlEndpoint;

    public function __construct(string $sparQlEndpoint, AdapterInterface $cacheAdapter, HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->sparQlEndpoint = $sparQlEndpoint;
        $this->cacheAdapter = $cacheAdapter;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $normalizers = [new SparQlResultDenormalizer()];
        $encoders = [new TrigEncoder(), new XmlEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function execute(StatementInterface $statement, bool $toTriples = false): array
    {
        $namespaces = '';
        foreach ($statement->getExtraNamespaces() as $prefix => $url) {
            $namespaces .= sprintf('%s:%s ', $prefix, $url);
        }
        $variables = implode(' ', array_map(function (Variable $variable) {
            return $variable->serialize();
        }, $statement->getVariables()));
        $conditions = sprintf('%s .', implode(' . ', $statement->getConditions()));
        $optionalConditions = '';
        foreach ($statement->getOptionalConditions() as $triple) {
            $optionalConditions .= sprintf('OPTIONAL {%s} .', $triple);
        }
        $query = match (get_class($statement)) {
            DeleteStatement::class => sprintf('%s DELETE %s WHERE {%s %s}', $namespaces, $variables, $conditions, $optionalConditions),
            SelectStatement::class => sprintf('%s SELECT %s WHERE {%s %s}', $namespaces, $variables, $conditions, $optionalConditions),
            UpdateStatement::class => sprintf('%s UPDATE %s WHERE {%s %s}', $namespaces, $variables, $conditions, $optionalConditions),
        };
        // Get cached select statement responses.
        $responseContent = null;
        $cachedContent = null;
        try {
            $cachedContent = $responseContent = match (get_class($statement)) {
                SelectStatement::class => $this->cacheAdapter->getItem($this->getKey($query))->get(),
            };
        } catch (InvalidArgumentException $exception) {
            $this->logger->info($exception->getMessage());
        }
        // If not cached, query triplestore.
        if ($responseContent === null) {
            $parameters = match (get_class($statement)) {
                DeleteStatement::class, UpdateStatement::class => ['body' => ['update' => $query]],
                SelectStatement::class => ['body' => ['query' => $query]],
            };
            try {
                $responseContent = $this->httpClient->request('POST', $this->sparQlEndpoint, $parameters)->getContent();
            } catch (HttpClientExceptionInterface $exception) {
                $this->logger->error($exception->getMessage());
                return [];
            }
        }
        // Update cache for successful select statement requests if needed.
        if (get_class($statement) === SelectStatement::class && $cachedContent !== $responseContent) {
            $queryKey = $this->getKey($query);
            /** @var TripleInterface $triple */
            foreach (array_merge($statement->getConditions(), $statement->getOptionalConditions()) as $triple) {
                try {
                    // Cache query key for triple subject.
                    $queryKeys = $this->cacheAdapter->getItem($this->getKey($triple->getSubject()->serialize()))->get();
                    $queryKeys = is_array($queryKeys) ? $queryKeys : [];
                    if (!in_array($queryKey, $queryKeys)) {
                        $queryKeys[] = $queryKey;
                        $this->cacheAdapter->getItem($this->getKey($triple->getSubject()->serialize()))->set($queryKeys);
                    }
                    // Cache query key for triple object.
                    $queryKeys = $this->cacheAdapter->getItem($this->getKey($triple->getObject()->serialize()))->get();
                    $queryKeys = is_array($queryKeys) ? $queryKeys : [];
                    if (!in_array($queryKey, $queryKeys)) {
                        $queryKeys[] = $queryKey;
                        $this->cacheAdapter->getItem($this->getKey($triple->getObject()->serialize()))->set($queryKeys);
                    }
                } catch (InvalidArgumentException $exception) {
                    $this->logger->info($exception->getMessage());
                }
            }
        }
        // Invalidate cache for delete and update statements.
        if (in_array(get_class($statement), [DeleteStatement::class, UpdateStatement::class])) {
            /** @var TripleInterface $triple */
            foreach (array_merge($statement->getConditions(), $statement->getOptionalConditions()) as $triple) {
                try {
                    // Invalidate any query key for triple subject.
                    $queryKeys = $this->cacheAdapter->getItem($this->getKey($triple->getSubject()->serialize()))->get();
                    if (is_array($queryKeys)) {
                        foreach ($queryKeys as $queryKey) {
                            $this->cacheAdapter->deleteItem($queryKey);
                        }
                    }
                    // Invalidate any query key for triple object.
                    $queryKeys = $this->cacheAdapter->getItem($this->getKey($triple->getObject()->serialize()))->get();
                    if (is_array($queryKeys)) {
                        foreach ($queryKeys as $queryKey) {
                            $this->cacheAdapter->deleteItem($queryKey);
                        }
                    }
                } catch (InvalidArgumentException $exception) {
                    $this->logger->info($exception->getMessage());
                }
            }
        }
        $sets = $this->serializer->deserialize($responseContent, SparQlResultDenormalizer::TYPE, 'xml');
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
            return $triples;
        }
        else {
            return $sets;
        }
    }

    public function delete(array $variables): DeleteStatement
    {
        return new DeleteStatement($variables);
    }

    public function select(array $variables): SelectStatement
    {
        return new SelectStatement($variables);
    }

    public function update(array $variables): UpdateStatement
    {
        return new UpdateStatement($variables);
    }
}
