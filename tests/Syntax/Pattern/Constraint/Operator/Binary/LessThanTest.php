<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\LessThan;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LessThanTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = '"1"^^xsd:integer < "2"^^xsd:integer';

    public function testOperator()
    {
        $term1 = new PlainLiteral(1);
        $term2 = new PlainLiteral(2);
        $operator = new LessThan($term1, $term2);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testInvalidOperator()
    {
        $term1 = new PlainLiteral('lorem');
        $term2 = new PlainLiteral(2);
        $this->expectException(InvalidArgumentException::class);
        new LessThan($term1, $term2);
    }
}