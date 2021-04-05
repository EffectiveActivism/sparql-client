<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Not;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NotTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = '! "lorem"';

    public function testOperator()
    {
        $term = new PlainLiteral('lorem');
        $operator = new Not($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
