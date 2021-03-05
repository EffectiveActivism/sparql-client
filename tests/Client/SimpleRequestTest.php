<?php declare(strict_types=1);

namespace Client;

use EffectiveActivism\SparQlClient\Syntax\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable;
use EffectiveActivism\SparQlClient\Client\SparQlClient;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class SimpleRequestTest extends KernelTestCase
{
    const BODY = '
        <sparql>
            <head>
                <variable name="subject"/>
                <variable name="object"/>
            </head>
            <results>
            </results>
        </sparql>
    ';

    /**
     * @covers \EffectiveActivism\SparQlClient\Client\SparQlClient
     */
    public function testSimpleRequest()
    {
        $receivedQuery = null;
        $httpClient = new MockHttpClient(function ($method, $url, $options) use (&$receivedQuery) {
            $receivedQuery = $options['body'];
            return new MockResponse(self::BODY);
        });
        $logger = $this->createMock(LoggerInterface::class);
        $sparQlClient = new SparQlClient($httpClient, $logger);
        $subject = new Variable('subject');
        $predicate = new Iri('schema:headline');
        $object = new Variable('object');
        $variables = [$subject, $object];
        $statement = $sparQlClient->select($variables);
        $statement
            ->condition(new Triple($subject, $predicate, $object));
        $triples = $sparQlClient->execute($statement);
        $this->assertIsArray($triples);
        $this->assertEquals('query=+SELECT+%3Fsubject+%3Fobject+WHERE+%7B%3Fsubject+%3Cschema%3Aheadline%3E+%3Fobject+.+%7D', $receivedQuery);
    }
}
