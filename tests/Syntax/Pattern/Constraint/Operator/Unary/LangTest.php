<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Lang;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LangTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'LANG("""lorem""")';

    const SERIALIZED_OPERATOR_VARIABLE = 'LANG(?subject)';

    public function testOperator()
    {
        $term = new PlainLiteral('lorem');
        $operator = new Lang($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testOperatorWithVariable()
    {
        $variable = new Variable('subject');
        $operator = new Lang($variable);
        $this->assertEquals(self::SERIALIZED_OPERATOR_VARIABLE, $operator->serialize());
    }

    public function testGetExpression()
    {
        $term = new PlainLiteral('lorem');
        $operator = new Lang($term);
        $this->assertEquals($term, $operator->getExpression());
    }
}
