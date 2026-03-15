<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Str;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectExpression\SelectExpression;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SelectExpressionTest extends KernelTestCase
{
    const SERIALIZED_EXPRESSION = '( STR(?subject) AS ?label )';

    public function testSelectExpression()
    {
        $subject = new Variable('subject');
        $label = new Variable('label');
        $strOp = new Str($subject);
        $expression = new SelectExpression($strOp, $label);
        $this->assertEquals(self::SERIALIZED_EXPRESSION, $expression->serialize());
        $this->assertSame($label, $expression->getVariable());
        $this->assertSame($strOp, $expression->getExpression());
    }
}
