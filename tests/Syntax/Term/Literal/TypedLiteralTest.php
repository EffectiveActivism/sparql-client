<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Literal;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TypedLiteralTest extends KernelTestCase
{
    public function testStringTypedLiteral()
    {
        $typedLiteral = new TypedLiteral('lorem');
        $this->assertEquals('"lorem"', $typedLiteral->serialize());
        $this->assertEquals('xsd:string', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral('lorem', new PrefixedIri('xsd', 'string'));
        $this->assertEquals('"lorem"^^xsd:string', $typedLiteral->serialize());
        $this->assertEquals('xsd:string', $typedLiteral->getType());
    }

    public function testIntegerTypedLiteral()
    {
        $typedLiteral = new TypedLiteral(2);
        $this->assertEquals('"2"^^xsd:integer', $typedLiteral->serialize());
        $this->assertEquals('xsd:integer', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral(2, new PrefixedIri('xsd', 'integer'));
        $this->assertEquals('"2"^^xsd:integer', $typedLiteral->serialize());
        $this->assertEquals('xsd:integer', $typedLiteral->getType());
    }

    public function testDoubleTypedLiteral()
    {
        $typedLiteral = new TypedLiteral(2.4);
        $this->assertEquals('"2.4"^^xsd:decimal', $typedLiteral->serialize());
        $this->assertEquals('xsd:decimal', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral(2.4, new PrefixedIri('xsd', 'decimal'));
        $this->assertEquals('"2.4"^^xsd:decimal', $typedLiteral->serialize());
        $this->assertEquals('xsd:decimal', $typedLiteral->getType());
    }

    public function testBooleanTypedLiteral()
    {
        $typedLiteral = new TypedLiteral(true);
        $this->assertEquals('"true"^^xsd:boolean', $typedLiteral->serialize());
        $this->assertEquals('xsd:boolean', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral(true, new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"true"^^xsd:boolean', $typedLiteral->serialize());
        $this->assertEquals('xsd:boolean', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral('false', new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"false"^^xsd:boolean', $typedLiteral->serialize());
        $this->assertEquals('xsd:boolean', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral(0, new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"false"^^xsd:boolean', $typedLiteral->serialize());
        $this->assertEquals('xsd:boolean', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral(22, new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"true"^^xsd:boolean', $typedLiteral->serialize());
        $this->assertEquals('xsd:boolean', $typedLiteral->getType());
    }

    public function testUnknownType()
    {
        $typedLiteral = new TypedLiteral('lorem', new PrefixedIri('xsd', 'unknown'));
        $this->expectException(InvalidArgumentException::class);
        $typedLiteral->getType();
    }
}
