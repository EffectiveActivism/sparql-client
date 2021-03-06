<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax;

use EffectiveActivism\SparQlClient\Syntax\Term\PlainLiteral;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlainLiteralTest extends KernelTestCase
{
    const INVALID_LITERALS = [
        '"""lorem"""',
        '\'\'\'lorem\'\'\'',
    ];

    const VALID_LITERALS = [
        '"""lorem"""' => 'lorem',
        '"4"^^xsd:integer' => 4,
        '"-4"^^xsd:integer' => -4,
        '"0"^^xsd:integer' => 0,
        '"1.4"^^xsd:decimal' => 1.4,
        '"0"^^xsd:decimal' => 0.0,
        '"true"^^xsd:boolean' => true,
        '"false"^^xsd:boolean' => false,
    ];

    public function testInvalidLiterals()
    {
        foreach (self::INVALID_LITERALS as $invalidLiteral) {
            $this->expectException(InvalidArgumentException::class);
            new PlainLiteral($invalidLiteral);
        }
    }

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
        $this->expectException(InvalidArgumentException::class);
        new PlainLiteral('lorem', 'latin');
    }
}
