<?php

namespace EffectiveActivism\SparQlClient\Tests\Constraint;

use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Binary\LessThanOrEqual;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LessThanOrEqualTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = '"1"^^xsd:integer <= "2"^^xsd:integer';

    public function testOperator()
    {
        $term1 = new PlainLiteral(1);
        $term2 = new PlainLiteral(2);
        $operator = new LessThanOrEqual($term1, $term2);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testInvalidOperator()
    {
        $term1 = new PlainLiteral('lorem');
        $term2 = new PlainLiteral(2);
        $this->expectException(InvalidArgumentException::class);
        new LessThanOrEqual($term1, $term2);
    }
}
