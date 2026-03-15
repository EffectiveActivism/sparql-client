<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\StrEnds;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StrEndsTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'STRENDS(?left,?right)';

    public function testOperator()
    {
        $left = new Variable('left');
        $right = new Variable('right');
        $operator = new StrEnds($left, $right);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
