<?php

namespace EffectiveActivism\SparQlClient\Tests\Constraint;

use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Unary\Negative;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NegativeTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = '- "12"^^xsd:integer';

    public function testOperator()
    {
        $term = new PlainLiteral(12);
        $operator = new Negative($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testInvalidOperator()
    {
        $term = new PlainLiteral('lorem');
        $this->expectException(InvalidArgumentException::class);
        new Negative($term);
    }
}
