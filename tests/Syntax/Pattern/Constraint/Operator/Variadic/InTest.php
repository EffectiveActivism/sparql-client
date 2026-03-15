<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Variadic;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Variadic\In;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = '?subject IN ("""lorem""", """ipsum""")';

    public function testOperator()
    {
        $subject = new Variable('subject');
        $a = new PlainLiteral('lorem');
        $b = new PlainLiteral('ipsum');
        $operator = new In($subject, $a, $b);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testGetExpressions()
    {
        $subject = new Variable('subject');
        $a = new PlainLiteral('lorem');
        $b = new PlainLiteral('ipsum');
        $operator = new In($subject, $a, $b);
        $this->assertEquals([$subject, $a, $b], $operator->getExpressions());
    }
}
