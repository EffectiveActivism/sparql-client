<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UuidTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'UUID()';

    public function testOperator()
    {
        $operator = new Uuid();
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
