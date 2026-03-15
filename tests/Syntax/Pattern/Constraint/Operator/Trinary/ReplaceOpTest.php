<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Trinary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Trinary\ReplaceOp;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReplaceOpTest extends KernelTestCase
{
    public function testReplaceThreeArgs()
    {
        $arg = new Variable('str');
        $pattern = new PlainLiteral('a');
        $replacement = new PlainLiteral('b');
        $operator = new ReplaceOp($arg, $pattern, $replacement);
        $this->assertEquals('REPLACE(?str,"""a""","""b""")', $operator->serialize());
    }

    public function testReplaceFourArgs()
    {
        $arg = new Variable('str');
        $pattern = new PlainLiteral('a');
        $replacement = new PlainLiteral('b');
        $flags = new PlainLiteral('i');
        $operator = new ReplaceOp($arg, $pattern, $replacement, $flags);
        $this->assertEquals('REPLACE(?str,"""a""","""b""","""i""")', $operator->serialize());
    }
}
