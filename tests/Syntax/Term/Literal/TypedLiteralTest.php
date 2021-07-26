<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Literal;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
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

    public function testDateTypedLiteral()
    {
        $typedLiteral = new TypedLiteral('2020-01-01+01:00', new PrefixedIri('xsd', 'date'));
        $this->assertEquals('"2020-01-01+01:00"^^xsd:date', $typedLiteral->serialize());
        $this->assertEquals('xsd:date', $typedLiteral->getType());
    }

    public function testDateTimeTypedLiteral()
    {
        $typedLiteral = new TypedLiteral('2020-01-01T12:01+01:00', new PrefixedIri('xsd', 'dateTime'));
        $this->assertEquals('"2020-01-01T12:01+01:00"^^xsd:dateTime', $typedLiteral->serialize());
        $this->assertEquals('xsd:dateTime', $typedLiteral->getType());
    }

    public function testTimeTypedLiteral()
    {
        $typedLiteral = new TypedLiteral('12:01+01:00', new PrefixedIri('xsd', 'time'));
        $this->assertEquals('"12:01+01:00"^^xsd:time', $typedLiteral->serialize());
        $this->assertEquals('xsd:time', $typedLiteral->getType());
    }

    public function testUnknownType()
    {
        $typedLiteral = new TypedLiteral('lorem', new PrefixedIri('xsd', 'unknown'));
        $this->expectException(SparQlException::class);
        $typedLiteral->getType();
    }
}
