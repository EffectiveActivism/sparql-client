<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Client;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable;
use EffectiveActivism\SparQlClient\Client\SparQlClient;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use EffectiveActivism\SparQlClient\Tests\Environment\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientRequestTest extends KernelTestCase
{
    const SELECT_STATEMENT_EXPECTED_QUERY = 'query=PREFIX rdf:http://www.w3.org/1999/02/22-rdf-syntax-ns#; PREFIX rdfs:http://www.w3.org/2000/01/rdf-schema#; PREFIX owl:http://www.w3.org/2002/07/owl#; PREFIX skos:http://www.w3.org/2004/02/skos/core#; PREFIX xsd:http://www.w3.org/2001/XMLSchema#; PREFIX schema:http://schema.org/;  SELECT { ?subject ?object } WHERE { ?subject schema:headline ?object . }';

    const SELECT_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY = 'query=PREFIX rdf:http://www.w3.org/1999/02/22-rdf-syntax-ns#; PREFIX rdfs:http://www.w3.org/2000/01/rdf-schema#; PREFIX owl:http://www.w3.org/2002/07/owl#; PREFIX skos:http://www.w3.org/2004/02/skos/core#; PREFIX xsd:http://www.w3.org/2001/XMLSchema#; PREFIX schema:http://schema.org/;  SELECT { ?subject ?object }';

    const INSERT_STATEMENT_EXPECTED_QUERY = 'update=PREFIX rdf:http://www.w3.org/1999/02/22-rdf-syntax-ns#; PREFIX rdfs:http://www.w3.org/2000/01/rdf-schema#; PREFIX owl:http://www.w3.org/2002/07/owl#; PREFIX skos:http://www.w3.org/2004/02/skos/core#; PREFIX xsd:http://www.w3.org/2001/XMLSchema#; PREFIX schema:http://schema.org/;  INSERT DATA { <urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece> schema:headline """Lorem Ipsum""" }';

    const DELETE_STATEMENT_EXPECTED_QUERY = 'update=PREFIX rdf:http://www.w3.org/1999/02/22-rdf-syntax-ns#; PREFIX rdfs:http://www.w3.org/2000/01/rdf-schema#; PREFIX owl:http://www.w3.org/2002/07/owl#; PREFIX skos:http://www.w3.org/2004/02/skos/core#; PREFIX xsd:http://www.w3.org/2001/XMLSchema#; PREFIX schema:http://schema.org/;  DELETE { ?subject schema:headline ?object } WHERE { ?subject rdf:type schema:Article . }';

    const DELETE_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY = 'update=PREFIX rdf:http://www.w3.org/1999/02/22-rdf-syntax-ns#; PREFIX rdfs:http://www.w3.org/2000/01/rdf-schema#; PREFIX owl:http://www.w3.org/2002/07/owl#; PREFIX skos:http://www.w3.org/2004/02/skos/core#; PREFIX xsd:http://www.w3.org/2001/XMLSchema#; PREFIX schema:http://schema.org/;  DELETE { ?subject schema:headline ?object }';

    const REPLACE_STATEMENT_EXPECTED_QUERY = 'update=PREFIX rdf:http://www.w3.org/1999/02/22-rdf-syntax-ns#; PREFIX rdfs:http://www.w3.org/2000/01/rdf-schema#; PREFIX owl:http://www.w3.org/2002/07/owl#; PREFIX skos:http://www.w3.org/2004/02/skos/core#; PREFIX xsd:http://www.w3.org/2001/XMLSchema#; PREFIX schema:http://schema.org/;  DELETE { ?subject schema:headline ?object } INSERT { ?subject schema:headline """Lorem Ipsum""" } WHERE { ?subject rdf:type schema:Article . }';

    const REPLACE_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY = 'update=PREFIX rdf:http://www.w3.org/1999/02/22-rdf-syntax-ns#; PREFIX rdfs:http://www.w3.org/2000/01/rdf-schema#; PREFIX owl:http://www.w3.org/2002/07/owl#; PREFIX skos:http://www.w3.org/2004/02/skos/core#; PREFIX xsd:http://www.w3.org/2001/XMLSchema#; PREFIX schema:http://schema.org/;  DELETE { ?subject schema:headline ?object } INSERT { ?subject schema:headline """Lorem Ipsum""" }';

    const HASHED_QUERY_UUID = '4006f4e9-f819-5a06-b744-1baea961fa5c';

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     */
    public function testSelectStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml'));
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClient::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $statement = $sparQlClient
            ->select([$subject, $object])
            ->where([new Triple($subject, $predicate, $object)]);
        $resultSet = $sparQlClient->execute($statement);
        $this->assertCount(2, $resultSet);
        $firstSet = $resultSet[0];
        $this->assertCount(3, $firstSet);
        $firstTerm = $firstSet[0];
        $this->assertInstanceOf(Iri::class, $firstTerm);
        $this->assertEquals('<urn:uuid:fcf19bc4-7e81-11eb-a169-175604c7c7bc>', $firstTerm->serialize());
        $this->assertEquals(self::SELECT_STATEMENT_EXPECTED_QUERY, urldecode($receivedQuery));
        $statement = $sparQlClient
            ->select([$subject, $object]);
        $sparQlClient->execute($statement);
        $this->assertEquals(self::SELECT_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY, urldecode($receivedQuery));
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     */
    public function testInsertStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse(null);
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClient::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $statement = $sparQlClient->insert(new Triple(new Iri('urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece'), new PrefixedIri('schema', 'headline'), new PlainLiteral('Lorem Ipsum')));
        $sparQlClient->execute($statement);
        $this->assertEquals(self::INSERT_STATEMENT_EXPECTED_QUERY, urldecode($receivedQuery));
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     */
    public function testDeleteStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse(null);
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClient::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $statement = $sparQlClient
            ->delete(new Triple(new Variable('subject'), new PrefixedIri('schema', 'headline'), new Variable('object')))
            ->where([new Triple(new Variable('subject'), new PrefixedIri('rdf', 'type'), new PrefixedIri('schema', 'Article'))]);
        $sparQlClient->execute($statement);
        $this->assertEquals(self::DELETE_STATEMENT_EXPECTED_QUERY, urldecode($receivedQuery));
        $statement = $sparQlClient
            ->delete(new Triple(new Variable('subject'), new PrefixedIri('schema', 'headline'), new Variable('object')));
        $sparQlClient->execute($statement);
        $this->assertEquals(self::DELETE_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY, urldecode($receivedQuery));
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     */
    public function testReplaceStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse(null);
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClient::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $statement = $sparQlClient
            ->replace(new Triple(new Variable('subject'), new PrefixedIri('schema', 'headline'), new Variable('object')))
            ->with(new Triple(new Variable('subject'), new PrefixedIri('schema', 'headline'), new PlainLiteral('Lorem Ipsum')))
            ->where([new Triple(new Variable('subject'), new PrefixedIri('rdf', 'type'), new PrefixedIri('schema', 'Article'))]);
        $sparQlClient->execute($statement);
        $this->assertEquals(self::REPLACE_STATEMENT_EXPECTED_QUERY, urldecode($receivedQuery));
        $statement = $sparQlClient
            ->replace(new Triple(new Variable('subject'), new PrefixedIri('schema', 'headline'), new Variable('object')))
            ->with(new Triple(new Variable('subject'), new PrefixedIri('schema', 'headline'), new PlainLiteral('Lorem Ipsum')));
        $sparQlClient->execute($statement);
        $this->assertEquals(self::REPLACE_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY, urldecode($receivedQuery));
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     */
    public function testSelectStatementRequestToTriples()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient(function ($method, $url, $options) {
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml'));
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClient::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Variable('subject');
        $predicate = new Variable('predicate');
        $object = new Variable('object');
        $variables = [$subject, $predicate, $object];
        $statement = $sparQlClient->select($variables);
        $statement
            ->where([new Triple($subject, $predicate, $object)]);
        $resultTripleSet = $sparQlClient->execute($statement, true);
        $this->assertCount(1, $resultTripleSet);
        /** @var TripleInterface $triple */
        $triple = $resultTripleSet[0];
        $this->assertInstanceOf(Triple::class, $triple);
        $this->assertEquals('<urn:uuid:fcf19bc4-7e81-11eb-a169-175604c7c7bc>', $triple->getSubject()->serialize());
        $this->assertEquals('subject', $triple->getSubject()->getVariableName());
        $this->assertEquals('<http://schema.org/headline>', $triple->getPredicate()->serialize());
        $this->assertEquals('predicate', $triple->getPredicate()->getVariableName());
        $this->assertEquals('"""Lorem"""', $triple->getObject()->serialize());
        $this->assertEquals('object', $triple->getObject()->getVariableName());
    }

    public function testCaching()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $selectResponseContent = file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml');
        $httpClient = new MockHttpClient([new MockResponse($selectResponseContent), new MockResponse(null), new MockResponse(null)]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClient::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $sparQlClient->execute($sparQlClient->select([$subject])->where([$triple]));
        $this->assertEquals($selectResponseContent, $cacheAdapter->get(self::HASHED_QUERY_UUID, function (ItemInterface $item) {
            return 'UNCACHED';
        }));
    }

    public function testCachingInvalidationForDeleteStatment()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $selectResponseContent = file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml');
        $httpClient = new MockHttpClient([new MockResponse($selectResponseContent), new MockResponse(null)]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClient::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $sparQlClient->execute($sparQlClient->select([$subject])->where([$triple]));
        $sparQlClient->execute($sparQlClient->delete($triple));
        $this->assertEquals('UNCACHED', $cacheAdapter->get(self::HASHED_QUERY_UUID, function (ItemInterface $item) {
            return 'UNCACHED';
        }));
    }

    public function testCachingInvalidationForReplaceStatment()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $selectResponseContent = file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml');
        $httpClient = new MockHttpClient([new MockResponse($selectResponseContent), new MockResponse(null)]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClient::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $sparQlClient->execute($sparQlClient->select([$subject])->where([$triple]));
        $sparQlClient->execute($sparQlClient->replace($triple)->with($triple));
        $this->assertEquals('UNCACHED', $cacheAdapter->get(self::HASHED_QUERY_UUID, function (ItemInterface $item) {
            return 'UNCACHED';
        }));
    }
}
