<?php declare(strict_types = 1);

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\StatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\UpdateStatement;
use EffectiveActivism\SparQlClient\Serializer\Encoder\TrigEncoder;
use EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlResultDenormalizer;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SparQlClient implements SparQlClientInterface
{
    protected HttpClientInterface $httpClient;

    protected LoggerInterface $logger;

    protected SerializerInterface $serializer;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $normalizers = [new SparQlResultDenormalizer()];
        $encoders = [new TrigEncoder(), new XmlEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function execute(StatementInterface $statement): array
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
        $parameters = match (get_class($statement)) {
            DeleteStatement::class, UpdateStatement::class => ['body' => ['update' => $query]],
            SelectStatement::class => ['body' => ['query' => $query]],
        };
        try {
            $response = $this->httpClient->request('POST', 'http://triplestore:9999/blazegraph/sparql', $parameters);
            return $this->serializer->deserialize($response->getContent(), 'rdf', 'xml');
        } catch (HttpClientExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());
        }
        return [];
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
