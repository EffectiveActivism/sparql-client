<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Aggregate;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\Max;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MaxTest extends KernelTestCase
{
    public function testMax()
    {
        $variable = new Variable('amount');
        $operator = new Max($variable);
        $this->assertEquals('MAX(?amount)', $operator->serialize());
    }

    public function testMaxDistinct()
    {
        $variable = new Variable('amount');
        $operator = new Max($variable);
        $operator->distinct();
        $this->assertEquals('MAX(DISTINCT ?amount)', $operator->serialize());
    }
}
