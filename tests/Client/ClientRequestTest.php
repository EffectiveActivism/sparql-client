<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Client;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri;
use EffectiveActivism\SparQlClient\Syntax\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable;
use EffectiveActivism\SparQlClient\Client\SparQlClient;
use EffectiveActivism\SparQlClient\Tests\Environment\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientRequestTest extends KernelTestCase
{
    const EXPECTED_QUERY = 'query= SELECT ?subject ?object WHERE {?subject schema:headline ?object . }';

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     */
    public function testSelectStatmentRequest()
    {
        $kernel = new TestKernel('test', true);
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/client-request.xml'));
        });
        $kernel->boot();
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        $sparQlClient = $kernel->getContainer()->get(SparQlClient::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $variables = [$subject, $object];
        $statement = $sparQlClient->select($variables);
        $statement
            ->condition(new Triple($subject, $predicate, $object));
        $resultSet = $sparQlClient->execute($statement);
        $this->assertCount(2, $resultSet);
        $firstSet = $resultSet[0];
        $this->assertCount(2, $firstSet);
        $firstTerm = $firstSet[0];
        $this->assertInstanceOf(Iri::class, $firstTerm);
        $this->assertEquals('<urn:uuid:fcf19bc4-7e81-11eb-a169-175604c7c7bc>', $firstTerm->serialize());
        $this->assertEquals(self::EXPECTED_QUERY, urldecode($receivedQuery));
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     */
    public function testSelectStatmentRequestToTriples()
    {
        $kernel = new TestKernel('test', true);
        $httpClient = new MockHttpClient(function ($method, $url, $options) {
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/client-request.xml'));
        });
        $kernel->boot();
        $kernel->getContainer()->set(HttpClientInterface::class, $httpClient);
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClient::class);
        $subject = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new Variable('object');
        $variables = [$subject, $object];
        $statement = $sparQlClient->select($variables);
        $statement
            ->condition(new Triple($subject, $predicate, $object));
        $resultTripleSet = $sparQlClient->execute($statement, true);
        $this->assertCount(1, $resultTripleSet);
        $triple = $resultTripleSet[0];
        $this->assertInstanceOf(Triple::class, $triple);
        $this->assertEquals('<urn:uuid:fcf19bc4-7e81-11eb-a169-175604c7c7bc>', $triple->getSubject()->serialize());
        $this->assertEquals('subject', $triple->getSubject()->getVariableName());
        $this->assertEquals('"""Lorem"""', $triple->getObject()->serialize());
        $this->assertEquals('object', $triple->getObject()->getVariableName());
    }
}
