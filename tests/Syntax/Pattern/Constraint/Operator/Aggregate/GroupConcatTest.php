<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Aggregate;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\GroupConcat;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GroupConcatTest extends KernelTestCase
{
    public function testGroupConcat()
    {
        $variable = new Variable('label');
        $operator = new GroupConcat($variable);
        $this->assertEquals('GROUP_CONCAT(?label)', $operator->serialize());
    }

    public function testGroupConcatWithSeparator()
    {
        $variable = new Variable('label');
        $operator = new GroupConcat($variable, ', ');
        $this->assertEquals('GROUP_CONCAT(?label; SEPARATOR=", ")', $operator->serialize());
    }

    public function testGroupConcatDistinct()
    {
        $variable = new Variable('label');
        $operator = new GroupConcat($variable);
        $operator->distinct();
        $this->assertEquals('GROUP_CONCAT(DISTINCT ?label)', $operator->serialize());
    }

    public function testGroupConcatDistinctWithSeparator()
    {
        $variable = new Variable('label');
        $operator = new GroupConcat($variable, '|');
        $operator->distinct();
        $this->assertEquals('GROUP_CONCAT(DISTINCT ?label; SEPARATOR="|")', $operator->serialize());
    }
}
