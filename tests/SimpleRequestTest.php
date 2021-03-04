<?php

namespace EffectiveActivism\SparQlClient\Tests;

use EffectiveActivism\SparQlClient\Primitive\Triple\Triple;
use EffectiveActivism\SparQlClient\Primitive\Term\Iri;
use EffectiveActivism\SparQlClient\Primitive\Term\Variable;
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
        $httpClient = new MockHttpClient([new MockResponse(self::BODY)]);
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
    }
}
