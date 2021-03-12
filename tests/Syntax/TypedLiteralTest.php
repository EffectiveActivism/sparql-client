<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax;

use EffectiveActivism\SparQlClient\Syntax\Term\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\TypedLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TypedLiteralTest extends KernelTestCase
{
    public function testIntegerTypedLiteral()
    {
        $typedLiteral = new TypedLiteral(2);
        $this->assertEquals('"2"^^xsd:integer', $typedLiteral->serialize());
        $typedLiteral = new TypedLiteral(2, new PrefixedIri('xsd', 'integer'));
        $this->assertEquals('"2"^^xsd:integer', $typedLiteral->serialize());
    }

    public function testDoubleTypedLiteral()
    {
        $typedLiteral = new TypedLiteral(2.4);
        $this->assertEquals('"2.4"^^xsd:decimal', $typedLiteral->serialize());
        $typedLiteral = new TypedLiteral(2.4, new PrefixedIri('xsd', 'decimal'));
        $this->assertEquals('"2.4"^^xsd:decimal', $typedLiteral->serialize());
    }

    public function testBooleanTypedLiteral()
    {
        $typedLiteral = new TypedLiteral(true, new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"true"^^xsd:boolean', $typedLiteral->serialize());
        $typedLiteral = new TypedLiteral('false', new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"false"^^xsd:boolean', $typedLiteral->serialize());
        $typedLiteral = new TypedLiteral(0, new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"false"^^xsd:boolean', $typedLiteral->serialize());
        $typedLiteral = new TypedLiteral(22, new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"true"^^xsd:boolean', $typedLiteral->serialize());
    }
}
