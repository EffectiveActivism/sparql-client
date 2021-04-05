<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\NotEqual;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NotEqualTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = '"true"^^xsd:boolean != "false"^^xsd:boolean';

    public function testOperator()
    {
        $term1 = new PlainLiteral(true);
        $term2 = new PlainLiteral(false);
        $operator = new NotEqual($term1, $term2);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testInvalidOperator()
    {
        $term1 = new PlainLiteral('lorem');
        $term2 = new PlainLiteral(2);
        $this->expectException(InvalidArgumentException::class);
        new NotEqual($term1, $term2);
    }
}
