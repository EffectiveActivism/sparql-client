<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Now;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NowTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'NOW()';

    public function testOperator()
    {
        $operator = new Now();
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
