<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Trinary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Trinary\IfOp;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IfOpTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'IF(?condition,?then,?else)';

    public function testOperator()
    {
        $condition = new Variable('condition');
        $then = new Variable('then');
        $else = new Variable('else');
        $operator = new IfOp($condition, $then, $else);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
