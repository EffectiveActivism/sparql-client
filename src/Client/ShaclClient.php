<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Serializer\Normalizer\RdfXmlDenormalizer;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatementInterface;
use EffectiveActivism\SparQlClient\Serializer\Encoder\NTripleDecoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ShaclClient implements ShaclClientInterface
{
    protected HttpClientInterface $httpClient;

    protected array $extraNamespaces = [];

    protected array $namespaces = [];

    protected SerializerInterface $serializer;

    protected string $shaclEndpoint;

    public function __construct(array $configuration, HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->namespaces = $configuration['namespaces'];
        $this->shaclEndpoint = $configuration['shacl_endpoint'];
        $encoders = [new NTripleDecoder()];
        $this->serializer = new Serializer([], $encoders);
    }

    public function validate(ConstructStatementInterface|DeleteStatementInterface|InsertStatementInterface|ReplaceStatementInterface $statement): bool
    {
        if ($statement instanceof ConstructStatementInterface) {
            $constructStatement = $statement;
        }
        else {
            $triples = match (get_class($statement)) {
                DeleteStatement::class => [$statement->getTripleToDelete()],
                InsertStatement::class => [$statement->getTripleToInsert()],
                ReplaceStatement::class => [$statement->getReplacement()],
            };
            $constructStatement = new ConstructStatement($triples, $this->getNamespaces());
            $constructStatement->where($statement->getConditions());
        }
        $data = [
            'contentToValidate' => $constructStatement->toQuery(),
            'reportSyntax' => 'application/rdf+xml',
        ];
        dump($data);
        $responseContent = $this->httpClient->request('POST', $this->shaclEndpoint, [
            'body' => json_encode($data),
        ])->getContent();
        $sets = $this->serializer->decode($responseContent, NTripleDecoder::FORMAT);
        dump($sets);
        return true;
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

    public function setExtraNamespaces(array $extraNamespaces): ShaclClientInterface
    {
        $this->extraNamespaces = $extraNamespaces;
        return $this;
    }
}
