<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Client;

use EffectiveActivism\SparQlClient\Client\ShaclClientInterface;
use EffectiveActivism\SparQlClient\Exception\ShaclException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Tests\Environment\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ShaclClientTest extends KernelTestCase
{
    const NAMESPACES = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> PREFIX owl: <http://www.w3.org/2002/07/owl#> PREFIX skos: <http://www.w3.org/2004/02/skos/core#> PREFIX xsd: <http://www.w3.org/2001/XMLSchema#> PREFIX schema: <http://schema.org/>';

    const SELECT_STATEMENT_EXPECTED_QUERY = 'query=' . self::NAMESPACES . ' SELECT ?subject ?object WHERE { ?subject schema:headline ?object . OPTIONAL { ?subject schema:headline ?object . } . }';

    public function testValidation()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient(function ($method, $url, $options) {
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/shacl-validation-request.ntriple'));
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var ShaclClientInterface $shaclClient */
        $shaclClient = $kernel->getContainer()->get(ShaclClientInterface::class);
        $shaclClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Iri('urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PlainLiteral('Lorem Ipsum');
        $insertStatement = new InsertStatement([new Triple($subject, $predicate, $object)], ['schema' => 'http://schema.org/']);
        $insertStatement->where([
            new Triple($subject, $predicate, new Variable('object')),
        ]);
        $statement = $shaclClient->convertToConstructStatement($insertStatement);
        $result = $shaclClient->validate($statement);
        $this->assertTrue($result->getStatus());
        $deleteStatement = new DeleteStatement([new Triple($subject, $predicate, $object)], ['schema' => 'http://schema.org/']);
        $deleteStatement->where([
            new Triple($subject, $predicate, new Variable('object')),
        ]);
        $statement = $shaclClient->convertToConstructStatement($deleteStatement);
        $result = $shaclClient->validate($statement);
        $this->assertTrue($result->getStatus());
        $replaceStatement = new ReplaceStatement([new Triple($subject, $predicate, $object)], ['schema' => 'http://schema.org/']);
        $replaceStatement->with([new Triple($subject, $predicate, $object)]);
        $replaceStatement->where([
            new Triple($subject, $predicate, new Variable('object')),
        ]);
        $statement = $shaclClient->convertToConstructStatement($replaceStatement);
        $result = $shaclClient->validate($statement);
        $this->assertTrue($result->getStatus());
        $constructStatement = new ConstructStatement([new Triple($subject, $predicate, $object)], ['schema' => 'http://schema.org/']);
        $constructStatement->where([
            new Triple($subject, $predicate, new Variable('object')),
        ]);
        $statement = $shaclClient->convertToConstructStatement($constructStatement);
        $result = $shaclClient->validate($statement);
        $this->assertTrue($result->getStatus());
    }

    public function testFailedValidation()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient(function ($method, $url, $options) {
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/shacl-validation-request-failed.ntriple'));
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var ShaclClientInterface $shaclClient */
        $shaclClient = $kernel->getContainer()->get(ShaclClientInterface::class);
        $shaclClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Iri('urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PlainLiteral('Lorem Ipsum');
        $statement = new InsertStatement([new Triple($subject, $predicate, $object)], ['schema' => 'http://schema.org/']);
        $statement->where([
            new Triple($subject, $predicate, new Variable('object')),
        ]);
        $result = $shaclClient->validate($shaclClient->convertToConstructStatement($statement));
        $this->assertFalse($result->getStatus());
        $this->assertEquals([
            'Sample validation failure message.'
        ], $result->getMessages());
    }

    public function testValidationException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient(function ($method, $url, $options) {
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/shacl-validation-request-failed.ntriple'));
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var ShaclClientInterface $shaclClient */
        $shaclClient = $kernel->getContainer()->get(ShaclClientInterface::class);
        $shaclClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Iri('urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PlainLiteral('Lorem Ipsum');
        $statement = new InsertStatement([new Triple(new Variable('unclausedSubject'), $predicate, $object)], ['schema' => 'http://schema.org/']);
        $statement->where([
            new Triple($subject, $predicate, new Variable('object')),
        ]);
        $this->expectException(ShaclException::class);
        $shaclClient->validate($shaclClient->convertToConstructStatement($statement));
    }
}
