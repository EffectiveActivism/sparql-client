<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Client;

use EffectiveActivism\SparQlClient\Syntax\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable;
use EffectiveActivism\SparQlClient\Client\SparQlClient;
use EffectiveActivism\SparQlClient\Tests\Environment\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SimpleRequestTest extends KernelTestCase
{
    const EXPECTED_QUERY = 'query= SELECT ?subject ?object WHERE {?subject schema:headline ?object . }';

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     */
    public function testSimpleRequest()
    {
        $kernel = new TestKernel('test', true);
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse(file_get_contents(__DIR__ . '/../fixtures/simple-request-test-result.xml'));
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
        $triples = $sparQlClient->execute($statement);
        $this->assertIsArray($triples);
        $this->assertEquals(self::EXPECTED_QUERY, urldecode($receivedQuery));
    }
}
