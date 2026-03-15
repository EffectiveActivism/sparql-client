<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Set;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\InversePath;
use EffectiveActivism\SparQlClient\Syntax\Term\Set\NegatedPropertySet;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NegatedPropertySetTest extends KernelTestCase
{
    const IRI = 'http://schema.org/headline';

    public function testNegatedPropertySet()
    {
        $predicate = new Iri(self::IRI);
        $negatedPropertySet = new NegatedPropertySet([$predicate]);
        $this->assertEquals(sprintf('!<%s>', self::IRI), $negatedPropertySet->serialize());
        $negatedPropertySet->setTerms([$predicate]);
        $this->assertEquals([$predicate], $negatedPropertySet->getTerms());
        $this->assertEquals(self::IRI, $negatedPropertySet->getRawValue());
        $negatedPropertySet->setVariableName('predicate');
        $this->assertEquals('predicate', $negatedPropertySet->getVariableName());
        $inversePredicate = new InversePath($predicate);
        $negatedPropertySet = new NegatedPropertySet([$inversePredicate]);
        $this->assertEquals(sprintf('!(^<%s>)', self::IRI), $negatedPropertySet->serialize());
        $this->assertEquals(self::IRI, $negatedPropertySet->getRawValue());
        $negatedPropertySet->setVariableName('predicate');
        $this->assertEquals('predicate', $negatedPropertySet->getVariableName());
        $negatedPropertySet = new NegatedPropertySet([$predicate, $inversePredicate]);
        $this->assertEquals(sprintf('!(<%s> | (^<%s>))', self::IRI, self::IRI), $negatedPropertySet->serialize());
        $negatedPropertySet->setTerms([]);
        $this->assertEquals('', $negatedPropertySet->getRawValue());
        $this->assertEquals('', $negatedPropertySet->getVariableName());
    }

    public function testNegatedPropertySetExceptions()
    {
        $literal = new PlainLiteral('lorem');
        $threwException = false;
        try {
            new NegatedPropertySet([$literal]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        $threwException = false;
        try {
            new NegatedPropertySet([]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
