<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Iri;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\RdfType;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RdfTypeTest extends KernelTestCase
{
    const SERIALIZED_VALUE = '<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>';

    const RAW_VALUE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';

    public function testRdfType()
    {
        $rdfType = new RdfType();
        $this->assertEquals(self::SERIALIZED_VALUE, $rdfType->serialize());
        $this->assertEquals(self::RAW_VALUE, $rdfType->getRawValue());
    }

    public function testRdfTypeUsedAsPredicateEmitsShorthand()
    {
        $subject = new Variable('s');
        $object = new Variable('o');
        $triple = new Triple($subject, new RdfType(), $object);
        $this->assertEquals('?s a ?o', $triple->serialize());
    }
}
