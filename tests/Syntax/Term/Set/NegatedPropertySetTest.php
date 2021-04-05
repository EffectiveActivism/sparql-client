<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Set;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\InversePath;
use EffectiveActivism\SparQlClient\Syntax\Term\Set\NegatedPropertySet;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NegatedPropertySetTest extends KernelTestCase
{
    const IRI = 'http://schema.org/headline';

    public function testNegatedPropertySet()
    {
        $negatedPropertySet = new NegatedPropertySet([]);
        $this->assertEquals('', $negatedPropertySet->getVariableName());
        $this->assertEquals('', $negatedPropertySet->getRawValue());
        $predicate = new Iri(self::IRI);
        $negatedPropertySet = new NegatedPropertySet([$predicate]);
        $this->assertEquals(sprintf('!<%s>', self::IRI), $negatedPropertySet->serialize());
        $negatedPropertySet->setTerms([$predicate]);
        $this->assertEquals([$predicate], $negatedPropertySet->getTerms());
        $inversePredicate = new InversePath($predicate);
        $negatedPropertySet = new NegatedPropertySet([$inversePredicate]);
        $this->assertEquals(sprintf('!(^<%s>)', self::IRI), $negatedPropertySet->serialize());
        $this->assertEquals(self::IRI, $negatedPropertySet->getRawValue());
        $negatedPropertySet->setVariableName('predicate');
        $this->assertEquals('predicate', $negatedPropertySet->getVariableName());
    }

    public function testNegatedPropertySetExceptions()
    {
        $literal = new PlainLiteral('lorem');
        $threwException = false;
        try {
            new NegatedPropertySet([$literal]);
        } catch (InvalidArgumentException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
