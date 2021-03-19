<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Client;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use EffectiveActivism\SparQlClient\Tests\Environment\TestKernel;
use InvalidArgumentException;
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
    const NAMESPACES = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>; PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>; PREFIX owl: <http://www.w3.org/2002/07/owl#>; PREFIX skos: <http://www.w3.org/2004/02/skos/core#>; PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>; PREFIX schema: <http://schema.org/>; ';

    const SELECT_STATEMENT_EXPECTED_QUERY = 'query=' . self::NAMESPACES . ' SELECT ?subject ?object  WHERE { ?subject schema:headline ?object . }';

    const INSERT_STATEMENT_EXPECTED_QUERY = 'update=' . self::NAMESPACES . ' INSERT { <urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece> schema:headline "Lorem Ipsum" } WHERE { <urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece> schema:headline ?object . }';

    const INSERT_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY = 'update=' . self::NAMESPACES . ' INSERT DATA { <urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece> schema:headline "Lorem Ipsum" }';

    const DELETE_STATEMENT_EXPECTED_QUERY = 'update=' . self::NAMESPACES . ' DELETE { ?subject schema:headline ?object } WHERE { ?subject rdf:type schema:Article . }';

    const DELETE_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY = 'update=' . self::NAMESPACES . ' DELETE DATA { <urn:uuid:e998469e-831e-11eb-95f2-a32290c912e6> schema:headline "Lorem"@la }';

    const REPLACE_STATEMENT_EXPECTED_QUERY = 'update=' . self::NAMESPACES . ' DELETE { ?subject schema:headline ?object } INSERT { ?subject schema:headline "Lorem Ipsum" } WHERE { ?subject rdf:type schema:Article . }';

    const HASHED_QUERY_UUID = 'c1bccd5e-4f4c-5aec-91ea-6bb85587c97f';

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement
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
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
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
        $firstTerm = $firstSet['subject'];
        $this->assertInstanceOf(Iri::class, $firstTerm);
        $this->assertEquals('<urn:uuid:fcf19bc4-7e81-11eb-a169-175604c7c7bc>', $firstTerm->serialize());
        $this->assertEquals(self::SELECT_STATEMENT_EXPECTED_QUERY, urldecode($receivedQuery));
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatement
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
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Iri('urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PlainLiteral('Lorem Ipsum');
        $statement = $sparQlClient
            ->insert(new Triple($subject, $predicate, $object))
            ->where([new Triple($subject, $predicate, new Variable('object'))]);
        $sparQlClient->execute($statement);
        $this->assertEquals(self::INSERT_STATEMENT_EXPECTED_QUERY, urldecode($receivedQuery));
        $statement = $sparQlClient
            ->insert(new Triple($subject, $predicate, $object));
        $sparQlClient->execute($statement);
        $this->assertEquals(self::INSERT_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY, urldecode($receivedQuery));
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement
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
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $statement = $sparQlClient
            ->delete(new Triple(new Variable('subject'), new PrefixedIri('schema', 'headline'), new Variable('object')))
            ->where([new Triple(new Variable('subject'), new PrefixedIri('rdf', 'type'), new PrefixedIri('schema', 'Article'))]);
        $sparQlClient->execute($statement);
        $this->assertEquals(self::DELETE_STATEMENT_EXPECTED_QUERY, urldecode($receivedQuery));
        $statement = $sparQlClient
            ->delete(new Triple(new Iri('urn:uuid:e998469e-831e-11eb-95f2-a32290c912e6'), new PrefixedIri('schema', 'headline'), new PlainLiteral('Lorem', 'la')));
        $sparQlClient->execute($statement);
        $this->assertEquals(self::DELETE_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY, urldecode($receivedQuery));
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatement
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
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $statement = $sparQlClient
            ->replace(new Triple(new Variable('subject'), new PrefixedIri('schema', 'headline'), new Variable('object')))
            ->with(new Triple(new Variable('subject'), new PrefixedIri('schema', 'headline'), new PlainLiteral('Lorem Ipsum')))
            ->where([new Triple(new Variable('subject'), new PrefixedIri('rdf', 'type'), new PrefixedIri('schema', 'Article'))]);
        $sparQlClient->execute($statement);
        $this->assertEquals(self::REPLACE_STATEMENT_EXPECTED_QUERY, urldecode($receivedQuery));
        $statement = $sparQlClient
            ->replace(new Triple(new Iri('urn:uuid:e998469e-831e-11eb-95f2-a32290c912e6'), new PrefixedIri('schema', 'headline'), new PlainLiteral('Lorem', 'la')))
            ->with(new Triple(new Iri('urn:uuid:e998469e-831e-11eb-95f2-a32290c912e6'), new PrefixedIri('schema', 'headline'), new PlainLiteral('Ipsum', 'la')));
        $this->expectException(InvalidArgumentException::class);
        $sparQlClient->execute($statement);
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
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
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
        $this->assertEquals('"Lorem"', $triple->getObject()->serialize());
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
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
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
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        // Cache select query.
        $sparQlClient->execute($sparQlClient->select([$subject])->where([$triple]));
        // Invalidate select query by IRI cache tags.
        $sparQlClient->execute($sparQlClient->delete($triple)->where([$triple]));
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
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $sparQlClient->execute($sparQlClient->select([$subject])->where([$triple]));
        $sparQlClient->execute($sparQlClient->replace($triple)->with($triple)->where([$triple]));
        $this->assertEquals('UNCACHED', $cacheAdapter->get(self::HASHED_QUERY_UUID, function (ItemInterface $item) {
            return 'UNCACHED';
        }));
    }
}
