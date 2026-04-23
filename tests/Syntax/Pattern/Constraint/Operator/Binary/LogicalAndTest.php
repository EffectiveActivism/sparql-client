<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\Contains;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\LogicalAnd;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LogicalAndTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = '"true"^^<http://www.w3.org/2001/XMLSchema#boolean> AND "false"^^<http://www.w3.org/2001/XMLSchema#boolean>';

    const SERIALIZED_NESTED_OPERATOR = 'CONTAINS(?a,"""x""") AND CONTAINS(?a,"""y""")';

    public function testOperator()
    {
        $term1 = new PlainLiteral(true);
        $term2 = new PlainLiteral(false);
        $operator = new LogicalAnd($term1, $term2);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testAcceptsNestedOperators()
    {
        $variable = new Variable('a');
        $left = new Contains($variable, new PlainLiteral('x'));
        $right = new Contains($variable, new PlainLiteral('y'));
        $operator = new LogicalAnd($left, $right);
        $this->assertEquals(self::SERIALIZED_NESTED_OPERATOR, $operator->serialize());
    }
}
