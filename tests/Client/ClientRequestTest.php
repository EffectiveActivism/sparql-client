<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Client;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Optionally\Optionally;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Tests\Environment\TestKernel;
use Exception;
use InvalidArgumentException;
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

class ClientRequestTest extends KernelTestCase
{
    const NAMESPACES = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> PREFIX owl: <http://www.w3.org/2002/07/owl#> PREFIX skos: <http://www.w3.org/2004/02/skos/core#> PREFIX xsd: <http://www.w3.org/2001/XMLSchema#> PREFIX schema: <http://schema.org/>';

    const SELECT_STATEMENT_EXPECTED_QUERY = 'query=' . self::NAMESPACES . ' SELECT ?subject ?object WHERE { ?subject schema:headline ?object . OPTIONAL { ?subject schema:headline ?object . } . }';

    const SELECT_STATEMENT_LIMIT_OFFSET_EXPECTED_QUERY = 'query=' . self::NAMESPACES . ' SELECT ?subject ?object WHERE { ?subject schema:headline ?object . } LIMIT 1 OFFSET 2';

    const INSERT_STATEMENT_EXPECTED_QUERY = 'update=' . self::NAMESPACES . ' INSERT { <urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece> schema:headline "Lorem Ipsum" } WHERE { <urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece> schema:headline ?object . }';

    const INSERT_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY = 'update=' . self::NAMESPACES . ' INSERT DATA { <urn:uuid:013acf16-80c6-11eb-95f8-c3d94b96fece> schema:headline "Lorem Ipsum" }';

    const DELETE_STATEMENT_EXPECTED_QUERY = 'update=' . self::NAMESPACES . ' DELETE { ?subject schema:headline ?object } WHERE { ?subject rdf:type schema:Article . }';

    const DELETE_STATEMENT_WITHOUT_CONDITION_EXPECTED_QUERY = 'update=' . self::NAMESPACES . ' DELETE DATA { <urn:uuid:e998469e-831e-11eb-95f2-a32290c912e6> schema:headline "Lorem"@la }';

    const REPLACE_STATEMENT_EXPECTED_QUERY = 'update=' . self::NAMESPACES . ' DELETE { ?subject schema:headline ?object } INSERT { ?subject schema:headline "Lorem Ipsum" } WHERE { ?subject rdf:type schema:Article . }';

    const HASHED_QUERY_UUID = '3959149f-83a7-53a6-82c8-7ca190789516';

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
            ->where([
                new Triple($subject, $predicate, $object),
                new Optionally([
                    new Triple($subject, $predicate, $object),
                ])
            ]);
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
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $statement = $sparQlClient
            ->select([$subject, $object])
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

    public function testClientSelectStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse(null, ['http_code' => 500])]);
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
        $statement = $sparQlClient->select([$subject])->where([$triple]);
        $threwException = false;
        try {
            $statement->limit(-1);
        } catch (InvalidArgumentException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        $threwException = false;
        try {
            $statement->offset(-1);
        } catch (InvalidArgumentException) {
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
        $httpClient = new MockHttpClient([new MockResponse(null)]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->select([$subject])->where([$triple]));
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
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->expects($this->at(0))->method('getItem')->willReturn($cacheItem);
        $cacheAdapterStub->expects($this->at(1))->method('getItem')->willThrowException($exceptionStub);
        $selectResponseContent = file_get_contents(__DIR__ . '/../fixtures/client-select-request.xml');
        $httpClient = new MockHttpClient([new MockResponse($selectResponseContent)]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->select([$subject])->where([$triple]));
    }

    public function testClientDeleteStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse(null, ['http_code' => 500])]);
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
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->delete($triple)->where([$triple]));
    }

    public function testClientDeleteStatementCacheException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareAdapter::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('invalidateTags')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse(null)]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->delete($triple)->where([$triple]));
    }

    public function testClientReplaceStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse(null, ['http_code' => 500])]);
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
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->replace($triple)->with($triple)->where([$triple]));
    }

    public function testClientReplaceStatementCacheException()
    {
        $cacheAdapterStub = $this->createMock(TagAwareAdapter::class);
        $exceptionStub = new class extends Exception implements CacheInvalidArgumentException {};
        $cacheAdapterStub->method('invalidateTags')->willThrowException($exceptionStub);
        $httpClient = new MockHttpClient([new MockResponse(null)]);
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $kernel->getContainer()->set(TagAwareCacheInterface::class, $cacheAdapterStub);
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PrefixedIri('schema', 'Article');
        $triple = new Triple($subject, $predicate, $object);
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->replace($triple)->with($triple)->where([$triple]));
    }

    public function testClientInsertStatementException()
    {
        $cacheAdapter = new TagAwareAdapter(new ArrayAdapter());
        $httpClient = new MockHttpClient([new MockResponse(null, ['http_code' => 500])]);
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
        $this->expectException(SparQlException::class);
        $sparQlClient->execute($sparQlClient->insert($triple)->where([$triple]));
    }
}
