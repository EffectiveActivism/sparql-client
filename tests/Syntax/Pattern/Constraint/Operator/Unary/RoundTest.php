<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Round;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RoundTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'ROUND(?subject)';

    public function testOperator()
    {
        $term = new Variable('subject');
        $operator = new Round($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testGetExpression()
    {
        $term = new Variable('subject');
        $operator = new Round($term);
        $this->assertEquals($term, $operator->getExpression());
    }
}
