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
    }

    public function testNegatedPropertySetEmptyTermsFallbacks()
    {
        $predicate = new Iri(self::IRI);
        $negatedPropertySet = new NegatedPropertySet([$predicate]);
        $reflection = new \ReflectionProperty(NegatedPropertySet::class, 'terms');
        $reflection->setValue($negatedPropertySet, []);
        $this->assertEquals('', $negatedPropertySet->getRawValue());
        $this->assertEquals('', $negatedPropertySet->getVariableName());
        $result = $negatedPropertySet->setVariableName('x');
        $this->assertInstanceOf(NegatedPropertySet::class, $result);
    }

    public function testNegatedPropertySetExceptions()
    {
        $predicate = new Iri(self::IRI);
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
        // setTerms([]) must also be rejected to preserve the invariant.
        $threwException = false;
        try {
            (new NegatedPropertySet([$predicate]))->setTerms([]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // setTerms() with an invalid term must also be rejected.
        $threwException = false;
        try {
            (new NegatedPropertySet([$predicate]))->setTerms([$literal]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
