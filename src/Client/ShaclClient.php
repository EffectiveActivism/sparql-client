<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Exception\ShaclException;
use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatementInterface;
use EffectiveActivism\SparQlClient\Serializer\Encoder\NTripleDecoder;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ShaclClient implements ShaclClientInterface
{
    protected HttpClientInterface $httpClient;

    protected array $extraNamespaces = [];

    protected LoggerInterface $logger;

    protected array $namespaces = [];

    protected SerializerInterface $serializer;

    protected string $shaclEndpoint;

    public function __construct(array $configuration, HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->namespaces = $configuration['namespaces'];
        $this->shaclEndpoint = $configuration['shacl_endpoint'];
        $encoders = [new NTripleDecoder()];
        $this->serializer = new Serializer([], $encoders);
    }

    /**
     * @throws ShaclException
     */
    public function validate(ConstructStatementInterface|DeleteStatementInterface|InsertStatementInterface|ReplaceStatementInterface $statement): bool
    {
        try {
            if ($statement instanceof ConstructStatementInterface) {
                $constructStatement = $statement;
            }
            else {
                $triples = match (get_class($statement)) {
                    DeleteStatement::class => $statement->getTriplesToDelete(),
                    InsertStatement::class => $statement->getTriplesToInsert(),
                    ReplaceStatement::class => $statement->getReplacements(),
                };
                $constructStatement = new ConstructStatement($triples, $this->getNamespaces());
                $constructStatement->where($statement->getConditions());
            }
            $query = $constructStatement->toQuery();
            $this->logger->debug($query);
            $data = [
                'contentToValidate' => $query,
                'reportSyntax' => 'application/rdf+xml',
            ];
            $responseContent = $this->httpClient->request('POST', $this->shaclEndpoint, [
                'body' => json_encode($data),
            ])->getContent();
            $sets = $this->serializer->decode($responseContent, NTripleDecoder::FORMAT);
            /** @var TripleInterface $triple */
            foreach ($sets as $triple) {
                if (
                    $triple->getPredicate()->serialize() === '<http://www.w3.org/ns/shacl#conforms>' &&
                    $triple->getObject() instanceof TypedLiteral &&
                    $triple->getObject()->getType() === 'xsd:boolean' &&
                    in_array($triple->getObject()->serialize(), ['"true"^^<http://www.w3.org/2001/XMLSchema#boolean>', '"true"^xsd:boolean'])
                ) {
                    $this->logger->debug('Validation succeeded');
                    return true;
                }
            }
        }
        catch (InvalidArgumentException|SparQlException|ExceptionInterface $exception) {
            $this->logger->debug('Validation errored: ' . $exception->getMessage());
            throw new ShaclException($exception->getMessage());
        }
        $this->logger->debug('Validation failed');
        return false;
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
