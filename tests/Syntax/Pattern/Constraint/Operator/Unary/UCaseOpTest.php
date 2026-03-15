<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\UCaseOp;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UCaseOpTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'UCASE(?subject)';

    public function testOperator()
    {
        $term = new Variable('subject');
        $operator = new UCaseOp($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
