<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\BNodeOp;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BNodeOpTest extends KernelTestCase
{
    public function testBNodeNoArg()
    {
        $operator = new BNodeOp();
        $this->assertEquals('BNODE()', $operator->serialize());
    }

    public function testBNodeWithArg()
    {
        $term = new Variable('subject');
        $operator = new BNodeOp($term);
        $this->assertEquals('BNODE(?subject)', $operator->serialize());
    }
}
