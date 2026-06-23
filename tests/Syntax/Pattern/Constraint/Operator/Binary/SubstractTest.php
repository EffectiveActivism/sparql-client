<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\Substract;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubstractTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = '"1"^^<http://www.w3.org/2001/XMLSchema#integer> - "2"^^<http://www.w3.org/2001/XMLSchema#integer>';

    const SERIALIZED_DECIMAL_OPERATOR = '?lat - "52.13"^^<http://www.w3.org/2001/XMLSchema#decimal>';

    public function testOperator()
    {
        $term1 = new PlainLiteral(1);
        $term2 = new PlainLiteral(2);
        $operator = new Substract($term1, $term2);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testDecimalOperand()
    {
        // Decimal (and other numeric) literal operands are accepted, e.g.
        // for "ABS(?lat - 52.13)" bounding-box arithmetic.
        $operator = new Substract(new Variable('lat'), new PlainLiteral(52.13));
        $this->assertEquals(self::SERIALIZED_DECIMAL_OPERATOR, $operator->serialize());
    }

    public function testInvalidOperator()
    {
        $term1 = new PlainLiteral('lorem');
        $term2 = new PlainLiteral('ipsum');
        $this->expectException(SparQlException::class);
        new Substract($term1, $term2);
    }
}
