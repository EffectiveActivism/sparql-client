<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Literal;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlainLiteralTest extends KernelTestCase
{
    const VALID_LITERALS = [
        '"""lorem"""' => 'lorem',
        '"4"^^xsd:integer' => 4,
        '"-4"^^xsd:integer' => -4,
        '"0"^^xsd:integer' => 0,
        '"1.4"^^xsd:decimal' => 1.4,
        '"0"^^xsd:decimal' => 0.0,
        '"true"^^xsd:boolean' => true,
        '"false"^^xsd:boolean' => false,
        "\"\"\"lorem\nipsum\"\"\"" => "lorem\nipsum",
    ];

    public function testValidLiterals()
    {
        foreach (array_values(self::VALID_LITERALS) as $validLiteral) {
            $this->assertInstanceOf(PlainLiteral::class, new PlainLiteral($validLiteral));
        }
    }

    public function testSerializedLiterals()
    {
        foreach(self::VALID_LITERALS as $expectedSerializedLiteral => $validLiteral) {
            $literal = new PlainLiteral($validLiteral);
            $this->assertEquals($expectedSerializedLiteral, $literal->serialize());
        }
    }

    public function testLiteralWithLanguage()
    {
        $this->assertInstanceOf(PlainLiteral::class, new PlainLiteral('lorem', 'la'));
    }

    public function testLiteralWithInvalidLanguage()
    {
        $this->expectException(SparQlException::class);
        new PlainLiteral('lorem', 'latin');
    }

    public function testLiteralWrappers()
    {
        $literal = new PlainLiteral('"lorem"');
        $this->assertEquals('"""\"lorem\""""', $literal->serialize());
        $literal = new PlainLiteral('\'"lorem"\'');
        $this->assertEquals('"""\\\'\"lorem\"\\\'"""', $literal->serialize());
        $literal = new PlainLiteral('\'"""lorem"""\'');
        $this->assertEquals('"""\\\'\"\"\"lorem\"\"\"\\\'"""', $literal->serialize());
        $literal = new PlainLiteral('\'"lorem"\'');
        $this->assertEquals('"""\\\'\"lorem\"\\\'"""', $literal->serialize());
        $literal = new PlainLiteral('"""\'\'\'lorem');
        $this->assertEquals('"""\"\"\"\\\'\\\'\\\'lorem"""', $literal->serialize());
    }

    public function testType()
    {
        $literal = new PlainLiteral(false);
        $this->assertEquals('xsd:boolean', $literal->getType());
        $literal = new PlainLiteral(12.3);
        $this->assertEquals('xsd:decimal', $literal->getType());
        $literal = new PlainLiteral(12);
        $this->assertEquals('xsd:integer', $literal->getType());
        $literal = new PlainLiteral('lorem');
        $this->assertEquals('xsd:string', $literal->getType());
    }
}
