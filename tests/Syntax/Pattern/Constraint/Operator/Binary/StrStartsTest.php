<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\StrStarts;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StrStartsTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'STRSTARTS(?left,?right)';

    public function testOperator()
    {
        $left = new Variable('left');
        $right = new Variable('right');
        $operator = new StrStarts($left, $right);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
