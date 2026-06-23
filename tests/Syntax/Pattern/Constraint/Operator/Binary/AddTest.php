<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\Add;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Bound;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AddTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = '"1"^^<http://www.w3.org/2001/XMLSchema#integer> + "2"^^<http://www.w3.org/2001/XMLSchema#integer>';

    const SERIALIZED_DECIMAL_OPERATOR = '"1.5"^^<http://www.w3.org/2001/XMLSchema#decimal> + "2.5"^^<http://www.w3.org/2001/XMLSchema#decimal>';

    public function testOperator()
    {
        $term1 = new PlainLiteral(1);
        $term2 = new PlainLiteral(2);
        $operator = new Add($term1, $term2);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testDecimalOperands()
    {
        $operator = new Add(new PlainLiteral(1.5), new PlainLiteral(2.5));
        $this->assertEquals(self::SERIALIZED_DECIMAL_OPERATOR, $operator->serialize());
    }

    public function testInvalidOperator()
    {
        $term1 = new PlainLiteral('lorem');
        $term2 = new PlainLiteral('ipsum');
        $this->expectException(SparQlException::class);
        new Add($term1, $term2);
    }

    public function testInvalidOperatorWithOperatorOperand()
    {
        // The other operand is an operator (no getRawValue()); building the
        // error message must not fatal — a SparQlException is thrown.
        $operatorOperand = new Bound(new Variable('x'));
        $nonNumericLiteral = new PlainLiteral('lorem');
        $this->expectException(SparQlException::class);
        new Add($operatorOperand, $nonNumericLiteral);
    }
}
