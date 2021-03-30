<?php

namespace EffectiveActivism\SparQlClient\Tests\Constraint;

use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Unary\IsLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IsLiteralTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'IsLITERAL("lorem")';

    public function testOperator()
    {
        $term = new PlainLiteral('lorem');
        $operator = new IsLiteral($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
