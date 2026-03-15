<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Trinary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Trinary\SubStr;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubStrTest extends KernelTestCase
{
    public function testSubStrTwoArgs()
    {
        $str = new Variable('str');
        $start = new Variable('start');
        $operator = new SubStr($str, $start);
        $this->assertEquals('SUBSTR(?str,?start)', $operator->serialize());
    }

    public function testSubStrThreeArgs()
    {
        $str = new Variable('str');
        $start = new Variable('start');
        $length = new Variable('length');
        $operator = new SubStr($str, $start, $length);
        $this->assertEquals('SUBSTR(?str,?start,?length)', $operator->serialize());
    }
}
