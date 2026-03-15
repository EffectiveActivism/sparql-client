<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\StrBefore;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StrBeforeTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'STRBEFORE(?left,?right)';

    public function testOperator()
    {
        $left = new Variable('left');
        $right = new Variable('right');
        $operator = new StrBefore($left, $right);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
