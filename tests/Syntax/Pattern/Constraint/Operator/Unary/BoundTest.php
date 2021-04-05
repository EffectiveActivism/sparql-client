<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Bound;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BoundTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'BOUND(?lorem)';

    public function testOperator()
    {
        $variable = new Variable('lorem');
        $operator = new Bound($variable);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
