<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Aggregate;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\Avg;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AvgTest extends KernelTestCase
{
    public function testAvg()
    {
        $variable = new Variable('amount');
        $operator = new Avg($variable);
        $this->assertEquals('AVG(?amount)', $operator->serialize());
    }

    public function testAvgDistinct()
    {
        $variable = new Variable('amount');
        $operator = new Avg($variable);
        $operator->distinct();
        $this->assertEquals('AVG(DISTINCT ?amount)', $operator->serialize());
    }
}
