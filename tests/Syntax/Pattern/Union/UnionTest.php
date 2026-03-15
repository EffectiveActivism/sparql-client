<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Union;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Union\Union;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UnionTest extends KernelTestCase
{
    const SERIALIZED_VALUE = '{ ?subject <http://schema.org/headline> """Lorem""" . } UNION { ?subject <http://schema.org/name> """Ipsum""" . }';

    public function testUnion()
    {
        $subject = new Variable('subject');
        $predicate1 = new Iri('http://schema.org/headline');
        $object1 = new PlainLiteral('Lorem');
        $triple1 = new Triple($subject, $predicate1, $object1);
        $predicate2 = new Iri('http://schema.org/name');
        $object2 = new PlainLiteral('Ipsum');
        $triple2 = new Triple($subject, $predicate2, $object2);
        $union = new Union([$triple1], [$triple2]);
        $this->assertEquals(self::SERIALIZED_VALUE, $union->serialize());
        $this->assertEquals([$subject, $predicate1, $object1, $subject, $predicate2, $object2], $union->getTerms());
        $this->assertEquals([$triple1, $triple2], $union->toArray());
    }

    public function testInvalidLeftPattern()
    {
        $this->expectException(SparQlException::class);
        new Union(['invalid pattern argument'], []);
    }

    public function testInvalidRightPattern()
    {
        $this->expectException(SparQlException::class);
        $subject = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral('Lorem');
        $triple = new Triple($subject, $predicate, $object);
        new Union([$triple], ['invalid pattern argument']);
    }
}
