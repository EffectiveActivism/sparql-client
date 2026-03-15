<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Aggregate;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\Count;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CountTest extends KernelTestCase
{
    public function testCountStar()
    {
        $operator = new Count();
        $this->assertEquals('COUNT(*)', $operator->serialize());
    }

    public function testCountExpression()
    {
        $variable = new Variable('subject');
        $operator = new Count($variable);
        $this->assertEquals('COUNT(?subject)', $operator->serialize());
    }

    public function testCountDistinct()
    {
        $variable = new Variable('subject');
        $operator = new Count($variable);
        $operator->distinct();
        $this->assertEquals('COUNT(DISTINCT ?subject)', $operator->serialize());
    }
}
