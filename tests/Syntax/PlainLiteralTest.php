<?php

namespace Syntax;

use EffectiveActivism\SparQlClient\Syntax\Term\PlainLiteral;
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
            $literal = new PlainLiteral($invalidLiteral);
            $this->assertFalse($literal->validate());
        }
    }

    public function testValidLiterals()
    {
        foreach (array_values(self::VALID_LITERALS) as $validLiteral) {
            $literal = new PlainLiteral($validLiteral);
            $this->assertTrue($literal->validate());
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
        $literal = new PlainLiteral('lorem', 'la');
    }
}
