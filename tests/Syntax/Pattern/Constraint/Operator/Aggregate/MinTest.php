<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Aggregate;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\Min;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MinTest extends KernelTestCase
{
    public function testMin()
    {
        $variable = new Variable('amount');
        $operator = new Min($variable);
        $this->assertEquals('MIN(?amount)', $operator->serialize());
    }

    public function testMinDistinct()
    {
        $variable = new Variable('amount');
        $operator = new Min($variable);
        $operator->distinct();
        $this->assertEquals('MIN(DISTINCT ?amount)', $operator->serialize());
    }
}
