<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Rand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RandTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'RAND()';

    public function testOperator()
    {
        $operator = new Rand();
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
