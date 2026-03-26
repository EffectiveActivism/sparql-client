<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Graph;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Graph\Graph;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GraphTest extends KernelTestCase
{
    const GRAPH_IRI = 'http://example.org/mygraph';

    const SERIALIZED_VALUE = 'GRAPH <http://example.org/mygraph> { ?subject <http://schema.org/headline> """Lorem""" . }';

    public function testGraph()
    {
        $graphIri = new Iri(self::GRAPH_IRI);
        $subject = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral('Lorem');
        $triple = new Triple($subject, $predicate, $object);
        $graph = new Graph($graphIri, [$triple]);
        $this->assertEquals(self::SERIALIZED_VALUE, $graph->serialize());
        $this->assertEquals([$graphIri, $subject, $predicate, $object], $graph->getTerms());
        $this->assertEquals([$triple], $graph->toArray());
        $this->assertEquals($graphIri, $graph->getGraph());
    }

    public function testGraphWithVariable()
    {
        $graphVariable = new Variable('g');
        $subject = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral('Lorem');
        $triple = new Triple($subject, $predicate, $object);
        $graph = new Graph($graphVariable, [$triple]);
        $this->assertEquals('GRAPH ?g { ?subject <http://schema.org/headline> """Lorem""" . }', $graph->serialize());
        $this->assertEquals([$graphVariable, $subject, $predicate, $object], $graph->getTerms());
        $this->assertEquals($graphVariable, $graph->getGraph());
    }

    public function testInvalidPattern()
    {
        $this->expectException(SparQlException::class);
        new Graph(new Iri(self::GRAPH_IRI), ['invalid pattern argument']);
    }
}
