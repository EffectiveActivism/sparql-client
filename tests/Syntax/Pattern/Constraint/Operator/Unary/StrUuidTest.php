<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\StrUuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StrUuidTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'STRUUID()';

    public function testOperator()
    {
        $operator = new StrUuid();
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
