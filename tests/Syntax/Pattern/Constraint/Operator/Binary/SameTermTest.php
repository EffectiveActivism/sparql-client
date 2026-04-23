<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\SameTerm;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Str;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SameTermTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'sameTERM("""lorem""","""ipsum""")';

    const SERIALIZED_NESTED_OPERATOR = 'sameTERM(STR(?a),STR(?b))';

    public function testOperator()
    {
        $term1 = new PlainLiteral('lorem');
        $term2 = new PlainLiteral('ipsum');
        $operator = new SameTerm($term1, $term2);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testAcceptsNestedOperators()
    {
        $left = new Str(new Variable('a'));
        $right = new Str(new Variable('b'));
        $operator = new SameTerm($left, $right);
        $this->assertEquals(self::SERIALIZED_NESTED_OPERATOR, $operator->serialize());
    }
}
