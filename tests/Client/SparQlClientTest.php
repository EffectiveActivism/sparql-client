<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Client;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Result\AskResultInterface;
use EffectiveActivism\SparQlClient\Result\ConstructResultInterface;
use EffectiveActivism\SparQlClient\Result\DescribeResultInterface;
use EffectiveActivism\SparQlClient\Result\SelectResultInterface;
use EffectiveActivism\SparQlClient\Result\UpdateResultInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Graph\Graph;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Optionally\Optionally;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Tests\Environment\TestKernel;
use Exception;
use Psr\Cache\InvalidArgumentException as CacheInvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SparQlClientTest extends KernelTestCase
{
    const SCHEMA_NAMESPACE = 'PREFIX schema: <http://schema.org/>';

    const RDF_AND_SCHEMA_NAMESPACES = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> PREFIX schema: <http://schema.org/>';

    const SELECT_STATEMENT_EXPECTED_QUERY = 'query=' . self::SCHEMA_NAMESPACE . ' SELECT ?subject ?object WHERE { ?subject schema:headline ?object . OPTIONAL { ?subject schema:headline ?object . } . }';

    const SELECT_STATEMENT_LIMIT_OFFSET_EXPECTED_QUERY = 'query=' . self::SCHEMA_NAMESPACE . ' SELECT ?subject ?object WHERE { ?subject schema:headline ?object . } LIMIT 1 OFFSET 2';

    const INSERT_STATEMENT_EXPECTED_QUERY = 'update=' . self::SCHEMA_NAMESPACE . ' INSERT { <urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece> schema:headline """Lorem Ipsum""" } WHERE { <urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece> schema:headline ?object . }';

    const INSERT_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY = 'update=' . self::SCHEMA_NAMESPACE . ' INSERT DATA { <urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece> schema:headline """Lorem Ipsum""" . <urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece> schema:headline """Lorem Ipsum""" }';

    const DELETE_STATEMENT_EXPECTED_QUERY = 'update=' . self::RDF_AND_SCHEMA_NAMESPACES . ' DELETE { ?subject schema:headline ?object } WHERE { ?subject rdf:type schema:Article . }';

    const DELETE_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY = 'update=' . self::SCHEMA_NAMESPACE . ' DELETE DATA { <urn:uuid:e998469e-831e-11eb-95f2-a32290c912e6> schema:headline """Lorem"""@la . <urn:uuid:e998469e-831e-11eb-95f2-a32290c912e6> schema:headline """Lorem"""@la }';

    const REPLACE_STATEMENT_EXPECTED_QUERY = 'update=' . self::RDF_AND_SCHEMA_NAMESPACES . ' DELETE { ?subject schema:headline ?object . ?subject schema:headline ?object } INSERT { ?subject schema:headline """Lorem Ipsum""" . ?subject schema:headline """Lorem Ipsum""" } WHERE { ?subject rdf:type schema:Article . }';

    const HASHED_QUERY_UUID = '1632e8b0-0c0f-5832-8050-5392e2d3a43f';

    const DESCRIBE_STATEMENT_EXPECTED_QUERY = 'query=' . self::SCHEMA_NAMESPACE . ' DESCRIBE ?subject WHERE { ?subject schema:alternateName """Lorem""" . }';

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
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $statement = $sparQlClient
            ->select([$subject, $object])
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([
                new Triple($subject, $predicate, $object),
                new Optionally([
                    new Triple($subject, $predicate, $object),
                ])
            ]);
        $result = $sparQlClient->execute($statement);
        $this->assertInstanceOf(SelectResultInterface::class, $result);
        $resultSet = $result->getRows();
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
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\AskStatement
     */
    public function testAskStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/client-ask-request.xml'));
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $statement = $sparQlClient
            ->ask()
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([
                new Triple($subject, $predicate, $object),
            ]);
        $result = $sparQlClient->execute($statement);
        $this->assertInstanceOf(AskResultInterface::class, $result);
        $this->assertTrue($result->getAnswer());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatement
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlConstructDenormalizer
     */
    public function testConstructStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/client-construct-request.xml'));
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'identifier');
        $object = new Variable('object');
        $statement = $sparQlClient
            ->construct([new Triple($subject, new PrefixedIri('rdf', 'type'), new PrefixedIri('schema', 'Person'))])
            ->withNamespaces(['rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'schema' => 'http://schema.org/'])
            ->where([
                new Triple($subject, $predicate, $object),
            ]);
        $result = $sparQlClient->execute($statement);
        $this->assertInstanceOf(ConstructResultInterface::class, $result);
        $resultSet = $result->getTriples();
        $this->assertCount(2, $resultSet);
        $this->assertInstanceOf(Triple::class, $resultSet[0]);
        $this->assertEquals('urn:uuid:d8c0c240-17a2-421e-8c24-49e75a1bddf0', $resultSet[0]->getSubject()->getRawValue());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatement
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlConstructDenormalizer
     */
    public function testConstructStatementRequestMultiple()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/client-construct-request-multiple.xml'));
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'identifier');
        $object = new Variable('object');
        $statement = $sparQlClient
            ->construct([new Triple($subject, new PrefixedIri('rdf', 'type'), new PrefixedIri('schema', 'Person'))])
            ->withNamespaces(['rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'schema' => 'http://schema.org/'])
            ->where([
                new Triple($subject, $predicate, $object),
            ]);
        $result = $sparQlClient->execute($statement);
        $this->assertInstanceOf(ConstructResultInterface::class, $result);
        $resultSet = $result->getTriples();
        $this->assertCount(4, $resultSet);
        $this->assertEquals('urn:uuid:d8c0c240-17a2-421e-8c24-49e75a1bddf0', $resultSet[0]->getSubject()->getRawValue());
        $this->assertEquals('https://schema.org/alternateName', $resultSet[1]->getPredicate()->getRawValue());
        $this->assertEquals('Ipsum', $resultSet[3]->getObject()->getRawValue());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\DescribeStatement
     */
    public function testDescribeStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/client-describe-request.xml'));
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'alternateName');
        $object = new PlainLiteral('Lorem');
        $statement = $sparQlClient
            ->describe([$subject])
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([new Triple($subject, $predicate, $object)]);
        $result = $sparQlClient->execute($statement);
        $this->assertInstanceOf(DescribeResultInterface::class, $result);
        $resultSet = $result->getTriples();
        $this->assertCount(1, $resultSet);
        $this->assertInstanceOf(Triple::class, $resultSet[0]);
        $this->assertEquals('urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639', $resultSet[0]->getSubject()->getRawValue());
        $this->assertEquals('Lorem', $resultSet[0]->getObject()->getRawValue());
        $this->assertEquals(self::DESCRIBE_STATEMENT_EXPECTED_QUERY, urldecode($receivedQuery));
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
            return new MockResponse('');
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Iri('urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PlainLiteral('Lorem Ipsum');
        $statement = $sparQlClient
            ->insert([new Triple($subject, $predicate, $object)])
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([new Triple($subject, $predicate, new Variable('object'))]);
        $result = $sparQlClient->execute($statement);
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertEquals(self::INSERT_STATEMENT_EXPECTED_QUERY, urldecode($receivedQuery));
        $triple = new Triple($subject, $predicate, $object);
        $statement = $sparQlClient
            ->insert([$triple, $triple])
            ->withNamespaces(['schema' => 'http://schema.org/']);
        $result = $sparQlClient->execute($statement);
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
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
            return new MockResponse('');
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $statement = $sparQlClient
            ->delete([new Triple(new Variable('subject'), new PrefixedIri('schema', 'headline'), new Variable('object'))])
            ->withNamespaces(['rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'schema' => 'http://schema.org/'])
            ->where([new Triple(new Variable('subject'), new PrefixedIri('rdf', 'type'), new PrefixedIri('schema', 'Article'))]);
        $result = $sparQlClient->execute($statement);
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertEquals(self::DELETE_STATEMENT_EXPECTED_QUERY, urldecode($receivedQuery));
        $triple = new Triple(new Iri('urn:uuid:e998469e-831e-11eb-95f2-a32290c912e6'), new PrefixedIri('schema', 'headline'), new PlainLiteral('Lorem', 'la'));
        $statement = $sparQlClient
            ->delete([$triple, $triple])
            ->withNamespaces(['schema' => 'http://schema.org/']);
        $result = $sparQlClient->execute($statement);
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
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
            return new MockResponse('');
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $tripleToReplace = new Triple(new Variable('subject'), new PrefixedIri('schema', 'headline'), new Variable('object'));
        $tripleToInsert = new Triple(new Variable('subject'), new PrefixedIri('schema', 'headline'), new PlainLiteral('Lorem Ipsum'));
        $statement = $sparQlClient
            ->replace([$tripleToReplace, $tripleToReplace])
            ->with([$tripleToInsert, $tripleToInsert])
            ->withNamespaces(['rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'schema' => 'http://schema.org/'])
            ->where([new Triple(new Variable('subject'), new PrefixedIri('rdf', 'type'), new PrefixedIri('schema', 'Article'))]);
        $result = $sparQlClient->execute($statement);
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertEquals(self::REPLACE_STATEMENT_EXPECTED_QUERY, urldecode($receivedQuery));
        $triple = new Triple(new Iri('urn:uuid:e998469e-831e-11eb-95f2-a32290c912e6'), new PrefixedIri('schema', 'headline'), new PlainLiteral('Lorem', 'la'));
        $statement = $sparQlClient
            ->replace([$triple])
            ->with([$triple])
            ->withNamespaces(['schema' => 'http://schema.org/']);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($statement);
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement
     */
    public function testSelectStatementWithLimitAndOffsetRequest()
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
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $statement = $sparQlClient
            ->select([$subject, $object])
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([new Triple($subject, $predicate, $object)])
            ->limit(1)
            ->offset(2);
        $sparQlClient->execute($statement);
        $this->assertEquals(self::SELECT_STATEMENT_LIMIT_OFFSET_EXPECTED_QUERY, urldecode($receivedQuery));
    }

    public function testCaching()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $selectResponseContent = file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml');
        $httpClient = new MockHttpClient([new MockResponse($selectResponseContent), new MockResponse(''), new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $sparQlClient->execute($sparQlClient->select([$subject])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
        $this->assertEquals($selectResponseContent, $cacheAdapter->get(self::HASHED_QUERY_UUID, function (ItemInterface $item) {
            return 'UNCACHED';
        }));
    }

    public function testCachingInvalidationForDeleteStatement()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $selectResponseContent = file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml');
        $httpClient = new MockHttpClient([new MockResponse($selectResponseContent), new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        // Cache select query.
        $sparQlClient->execute($sparQlClient->select([$subject])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
        // Invalidate select query by IRI cache tags.
        $sparQlClient->execute($sparQlClient->delete([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
        $this->assertEquals('UNCACHED', $cacheAdapter->get(self::HASHED_QUERY_UUID, function (ItemInterface $item) {
            return 'UNCACHED';
        }));
    }

    public function testCachingInvalidationForReplaceStatement()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $selectResponseContent = file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml');
        $httpClient = new MockHttpClient([new MockResponse($selectResponseContent), new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $sparQlClient->execute($sparQlClient->select([$subject])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
        $sparQlClient->execute($sparQlClient->replace([$triple])->with([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
        $this->assertEquals('UNCACHED', $cacheAdapter->get(self::HASHED_QUERY_UUID, function (ItemInterface $item) {
            return 'UNCACHED';
        }));
    }

    public function testClientSelectStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $statement = $sparQlClient->select([$subject])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]);
        $threwException = false;
        try {
            $statement->limit(-1);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        $threwException = false;
        try {
            $statement->offset(-1);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        $threwException = false;
        try {
            $sparQlClient->execute($statement);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }

    public function testClientSelectStatementCacheException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareAdapter::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('get')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->select([$subject])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientSelectStatementCacheSaveException()
    {
        $cacheAdapterStub = $this->getMockBuilder(TagAwareAdapter::class)
            ->setConstructorArgs([new ArrayAdapter()])
            ->onlyMethods(['getItem'])
            ->getMock();
        $cacheItem = new CacheItem();
        $reflectedCacheItem = new \ReflectionObject($cacheItem);
        $reflectedProperty = $reflectedCacheItem->getProperty('key');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($cacheItem, 'foo');
        $cacheAdapterStub->expects($this->exactly(1))->method('getItem')->willReturn($cacheItem);
        $selectResponseContent = file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml');
        $httpClient = new MockHttpClient([new MockResponse($selectResponseContent)]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->select([$subject])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientAskStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->ask()->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientAskStatementCacheException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareAdapter::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('get')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->ask()->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientAskStatementCacheSaveException()
    {
        $cacheAdapterStub = $this->getMockBuilder(TagAwareAdapter::class)
            ->setConstructorArgs([new ArrayAdapter()])
            ->onlyMethods(['getItem'])
            ->getMock();
        $cacheItem = new CacheItem();
        $reflectedCacheItem = new \ReflectionObject($cacheItem);
        $reflectedProperty = $reflectedCacheItem->getProperty('key');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($cacheItem, 'foo');
        $cacheAdapterStub->expects($this->exactly(1))->method('getItem')->willReturn($cacheItem);
        $askResponseContent = file_get_contents(__DIR__ . '/../fixtures/client-ask-request.xml');
        $httpClient = new MockHttpClient([new MockResponse($askResponseContent)]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->ask()->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientConstructStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $statement = $sparQlClient->construct([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]);
        $threwException = false;
        try {
            $sparQlClient->execute($statement);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }

    public function testClientConstructStatementCacheException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareAdapter::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('get')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->construct([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientConstructStatementCacheSaveException()
    {
        $cacheAdapterStub = $this->getMockBuilder(TagAwareAdapter::class)
            ->setConstructorArgs([new ArrayAdapter()])
            ->onlyMethods(['getItem'])
            ->getMock();
        $cacheItem = new CacheItem();
        $reflectedCacheItem = new \ReflectionObject($cacheItem);
        $reflectedProperty = $reflectedCacheItem->getProperty('key');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($cacheItem, 'foo');
        $cacheAdapterStub->expects($this->exactly(1))->method('getItem')->willReturn($cacheItem);
        $selectResponseContent = file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml');
        $httpClient = new MockHttpClient([new MockResponse($selectResponseContent)]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->construct([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientDeleteStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->delete([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientDeleteStatementCacheException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareAdapter::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('invalidateTags')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->delete([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientReplaceStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->replace([$triple])->with([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientReplaceStatementCacheException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareAdapter::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('invalidateTags')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->replace([$triple])->with([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientInsertStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->insert([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientDescribeStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $resource = new Iri('urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece');
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->describe([$resource]));
    }

    public function testClientDescribeStatementCacheException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareAdapter::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('get')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $resource = new Iri('urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece');
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->describe([$resource]));
    }

    public function testClientDescribeStatementCacheSaveException()
    {
        $cacheAdapterStub = $this->getMockBuilder(TagAwareAdapter::class)
            ->setConstructorArgs([new ArrayAdapter()])
            ->onlyMethods(['getItem'])
            ->getMock();
        $cacheItem = new CacheItem();
        $reflectedCacheItem = new \ReflectionObject($cacheItem);
        $reflectedProperty = $reflectedCacheItem->getProperty('key');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($cacheItem, 'foo');
        $cacheAdapterStub->expects($this->exactly(1))->method('getItem')->willReturn($cacheItem);
        $selectResponseContent = file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml');
        $httpClient = new MockHttpClient([new MockResponse($selectResponseContent)]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $resource = new Iri('urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece');
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->describe([$resource]));
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\ClearStatement
     */
    public function testClearGraphStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse('');
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $graph = new Iri('http://example.org/g');
        $result = $sparQlClient->execute($sparQlClient->clearGraph($graph));
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertEquals('update=CLEAR GRAPH <http://example.org/g>', urldecode($receivedQuery));
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\DropStatement
     */
    public function testDropGraphStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse('');
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $graph = new Iri('http://example.org/g');
        $result = $sparQlClient->execute($sparQlClient->dropGraph($graph));
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertEquals('update=DROP GRAPH <http://example.org/g>', urldecode($receivedQuery));
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\CreateStatement
     */
    public function testCreateGraphStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse('');
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $graph = new Iri('http://example.org/g');
        $result = $sparQlClient->execute($sparQlClient->createGraph($graph));
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertEquals('update=CREATE GRAPH <http://example.org/g>', urldecode($receivedQuery));
    }

    public function testClientClearStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $graph = new Iri('http://example.org/g');
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->clearGraph($graph));
    }

    public function testClientCreateStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $graph = new Iri('http://example.org/g');
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->createGraph($graph));
    }

    public function testClientDropStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $graph = new Iri('http://example.org/g');
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->dropGraph($graph));
    }

    public function testInsertStatementExceptionContainsStatusCodeAndBody()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('query parse failure', ['http_code' => 400])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Iri('urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PlainLiteral('Lorem Ipsum');
        $statement = $sparQlClient
            ->insert([new Triple($subject, $predicate, $object)])
            ->withNamespaces(['schema' => 'http://schema.org/']);
        try {
            $sparQlClient->execute($statement);
            $this->fail('Expected SparQlException was not thrown');
        } catch (SparQlException $exception) {
            $this->assertSame(400, $exception->getStatusCode());
            $this->assertSame('query parse failure', $exception->getResponseBody());
            $this->assertStringContainsString('INSERT', $exception->getQuery());
        }
    }

    public function testDeleteStatementExceptionContainsStatusCodeAndBody()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('unknown predicate', ['http_code' => 400])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $triple = new Triple($subject, $predicate, $object);
        try {
            $sparQlClient->execute($sparQlClient->delete([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
            $this->fail('Expected SparQlException was not thrown');
        } catch (SparQlException $exception) {
            $this->assertSame(400, $exception->getStatusCode());
            $this->assertSame('unknown predicate', $exception->getResponseBody());
            $this->assertStringContainsString('DELETE', $exception->getQuery());
        }
    }

    public function testSelectStatementExceptionContainsStatusCodeAndBody()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('syntax error near token', ['http_code' => 400])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $triple = new Triple($subject, $predicate, $object);
        try {
            $sparQlClient->execute($sparQlClient->select([$subject])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
            $this->fail('Expected SparQlException was not thrown');
        } catch (SparQlException $exception) {
            $this->assertSame(400, $exception->getStatusCode());
            $this->assertSame('syntax error near token', $exception->getResponseBody());
            $this->assertStringContainsString('SELECT', $exception->getQuery());
        }
    }

    public function testAskStatementWithGraphPattern()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient(function ($method, $url, $options) {
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/client-ask-request.xml'));
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $graphIri = new Iri('http://example.org/g');
        $triple = new Triple($subject, $predicate, $object);
        $statement = $sparQlClient
            ->ask()
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([new Graph($graphIri, [$triple])]);
        $result = $sparQlClient->execute($statement);
        $this->assertInstanceOf(AskResultInterface::class, $result);
        $this->assertTrue($result->getAnswer());
    }

    public function testClientAskStatementInvalidXmlResponse()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient(function ($method, $url, $options) {
            return new MockResponse('<?xml version="1.0"?><sparql/>');
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->ask()->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientSelectStatementCachedDeserializationException()
    {
        // XML with a binding type that SparQlResultDenormalizer doesn't recognise, causing InvalidResultException.
        $invalidSparqlXml = '<?xml version="1.0"?><sparql><results><result><binding name="subject"><unknowntype>value</unknowntype></binding></result></results></sparql>';
        $cacheAdapterStub = $this->createMock(TagAwareCacheInterface::class);
        // Returning directly (bypassing the callback) leaves $rows null, forcing the post-cache deserialise path.
        $cacheAdapterStub->method('get')->willReturn($invalidSparqlXml);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->select([$subject])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientClearStatementCacheInvalidationException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareCacheInterface::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('invalidateTags')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $graph = new Iri('http://example.org/g');
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->clearGraph($graph));
    }

    public function testClientDropStatementCacheInvalidationException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareCacheInterface::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('invalidateTags')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $graph = new Iri('http://example.org/g');
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->dropGraph($graph));
    }

    public function testClientInsertStatementCacheInvalidationException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareCacheInterface::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('invalidateTags')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->insert([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientLoadStatementCacheInvalidationException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareCacheInterface::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('invalidateTags')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->load(new Iri('http://example.org/data'))->into(new Iri('http://example.org/g')));
    }

    public function testClientCopyStatementCacheInvalidationException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareCacheInterface::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('invalidateTags')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->copyGraph(new Iri('http://example.org/src'), new Iri('http://example.org/dst')));
    }

    public function testClientMoveStatementCacheInvalidationException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareCacheInterface::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('invalidateTags')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->moveGraph(new Iri('http://example.org/src'), new Iri('http://example.org/dst')));
    }

    public function testClientAddStatementCacheInvalidationException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareCacheInterface::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('invalidateTags')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse('')]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->addGraph(new Iri('http://example.org/src'), new Iri('http://example.org/dst')));
    }

    public function testClientStatementWithRequestThrowingException()
    {
        // When request() throws before $response is assigned, resolveStatusCode/resolveBody receive null.
        $transportException = new class('connection failed') extends \RuntimeException implements \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface {};
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock->method('request')->willThrowException($transportException);
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClientMock);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->insert([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testClientStatementWithFailingResponseMethods()
    {
        // When getContent() throws and getStatusCode()/getContent(false) also throw,
        // resolveStatusCode and resolveBody hit their Throwable catch branches.
        $transportException = new class('transport error') extends \RuntimeException implements \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface {};
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getContent')->willThrowException($transportException);
        $responseMock->method('getStatusCode')->willThrowException(new \RuntimeException('status unavailable'));
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock->method('request')->willReturn($responseMock);
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClientMock);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->insert([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]));
    }

    public function testLoadStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse('');
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $source = new Iri('http://example.org/data');
        $result = $sparQlClient->execute($sparQlClient->load($source));
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertEquals('update=LOAD <http://example.org/data>', urldecode($receivedQuery));
    }

    public function testLoadIntoGraphStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse('');
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $source = new Iri('http://example.org/data');
        $graph = new Iri('http://example.org/g');
        $result = $sparQlClient->execute($sparQlClient->load($source)->into($graph));
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
        $this->assertEquals('update=LOAD <http://example.org/data> INTO GRAPH <http://example.org/g>', urldecode($receivedQuery));
    }

    public function testCopyGraphStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse('');
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $src = new Iri('http://example.org/src');
        $dst = new Iri('http://example.org/dst');
        $result = $sparQlClient->execute($sparQlClient->copyGraph($src, $dst));
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertEquals('update=COPY GRAPH <http://example.org/src> TO GRAPH <http://example.org/dst>', urldecode($receivedQuery));
    }

    public function testMoveGraphStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse('');
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $src = new Iri('http://example.org/src');
        $dst = new Iri('http://example.org/dst');
        $result = $sparQlClient->execute($sparQlClient->moveGraph($src, $dst));
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertEquals('update=MOVE GRAPH <http://example.org/src> TO GRAPH <http://example.org/dst>', urldecode($receivedQuery));
    }

    public function testAddGraphStatementRequest()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse('');
        });
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $src = new Iri('http://example.org/src');
        $dst = new Iri('http://example.org/dst');
        $result = $sparQlClient->execute($sparQlClient->addGraph($src, $dst));
        $this->assertInstanceOf(UpdateResultInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertEquals('update=ADD GRAPH <http://example.org/src> TO GRAPH <http://example.org/dst>', urldecode($receivedQuery));
    }

    public function testClientLoadStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->load(new Iri('http://example.org/data')));
    }

    public function testClientCopyStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->copyGraph(new Iri('http://example.org/src'), new Iri('http://example.org/dst')));
    }

    public function testClientMoveStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->moveGraph(new Iri('http://example.org/src'), new Iri('http://example.org/dst')));
    }

    public function testClientAddStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 500])]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->addGraph(new Iri('http://example.org/src'), new Iri('http://example.org/dst')));
    }

    public function testEndpointRoutingAndAcceptHeaders()
    {
        $selectXml = file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml');
        $askXml = file_get_contents(__DIR__ . '/../fixtures/client-ask-request.xml');
        $constructXml = file_get_contents(__DIR__ . '/../fixtures/client-construct-request.xml');
        $describeXml = file_get_contents(__DIR__ . '/../fixtures/client-describe-request.xml');
        $receivedUrl = null;
        $receivedHeaders = null;
        // Query operations should hit query_endpoint with correct Accept headers.
        $operations = [
            'select' => ['fixture' => $selectXml, 'expectedAccept' => 'application/sparql-results+xml'],
            'ask' => ['fixture' => $askXml, 'expectedAccept' => 'application/sparql-results+xml'],
            'construct' => ['fixture' => $constructXml, 'expectedAccept' => 'application/rdf+xml'],
            'describe' => ['fixture' => $describeXml, 'expectedAccept' => 'application/rdf+xml'],
        ];
        foreach ($operations as $type => $config) {
            $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
            $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedUrl, &$receivedHeaders, $config) {
                $receivedUrl = $url;
                $receivedHeaders = $options['headers'];
                return new MockResponse($config['fixture']);
            });
            $kernel = new TestKernel('test', true);
            $kernel->boot();
            $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
            $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
            /** @var SparQlClientInterface $sparQlClient */
            $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
            $subject = new Variable('subject');
            $predicate = new PrefixedIri('schema', 'headline');
            $object = new Variable('object');
            $triple = new Triple($subject, $predicate, $object);
            $statement = match ($type) {
                'select' => $sparQlClient->select([$subject])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]),
                'ask' => $sparQlClient->ask()->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]),
                'construct' => $sparQlClient->construct([$triple])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]),
                'describe' => $sparQlClient->describe([$subject])->withNamespaces(['schema' => 'http://schema.org/'])->where([$triple]),
            };
            $sparQlClient->execute($statement);
            $this->assertEquals('http://test-sparql-endpoint:9999/blazegraph/sparql', $receivedUrl, "Query operation '$type' should hit query_endpoint");
            $this->assertContains('Accept: ' . $config['expectedAccept'], $receivedHeaders, "Query operation '$type' should send correct Accept header");
        }
        // Update operations should hit update_endpoint.
        $updateOperations = ['insert', 'delete', 'clear', 'create', 'drop', 'load', 'copy', 'move', 'add'];
        foreach ($updateOperations as $type) {
            $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
            $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedUrl, &$receivedHeaders) {
                $receivedUrl = $url;
                $receivedHeaders = $options['headers'];
                return new MockResponse('');
            });
            $kernel = new TestKernel('test', true);
            $kernel->boot();
            $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapter);
            $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
            /** @var SparQlClientInterface $sparQlClient */
            $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
            $subject = new Iri('urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece');
            $predicate = new PrefixedIri('schema', 'headline');
            $object = new PlainLiteral('Lorem');
            $triple = new Triple($subject, $predicate, $object);
            $graph = new Iri('http://example.org/g');
            $graph2 = new Iri('http://example.org/g2');
            $statement = match ($type) {
                'insert' => $sparQlClient->insert([$triple])->withNamespaces(['schema' => 'http://schema.org/']),
                'delete' => $sparQlClient->delete([$triple])->withNamespaces(['schema' => 'http://schema.org/']),
                'clear' => $sparQlClient->clearGraph($graph),
                'create' => $sparQlClient->createGraph($graph),
                'drop' => $sparQlClient->dropGraph($graph),
                'load' => $sparQlClient->load($graph),
                'copy' => $sparQlClient->copyGraph($graph, $graph2),
                'move' => $sparQlClient->moveGraph($graph, $graph2),
                'add' => $sparQlClient->addGraph($graph, $graph2),
            };
            $sparQlClient->execute($statement);
            $this->assertEquals('http://test-sparql-endpoint:9999/blazegraph/sparql', $receivedUrl, "Update operation '$type' should hit update_endpoint");
        }
    }
}
