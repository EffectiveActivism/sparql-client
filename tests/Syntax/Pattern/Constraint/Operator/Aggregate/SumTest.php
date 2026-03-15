<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Aggregate;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\Sum;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SumTest extends KernelTestCase
{
    public function testSum()
    {
        $variable = new Variable('amount');
        $operator = new Sum($variable);
        $this->assertEquals('SUM(?amount)', $operator->serialize());
    }

    public function testSumDistinct()
    {
        $variable = new Variable('amount');
        $operator = new Sum($variable);
        $operator->distinct();
        $this->assertEquals('SUM(DISTINCT ?amount)', $operator->serialize());
    }
}
