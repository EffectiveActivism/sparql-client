<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Variadic;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Variadic\Coalesce;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CoalesceTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'COALESCE(?a, ?b)';

    public function testOperator()
    {
        $a = new Variable('a');
        $b = new Variable('b');
        $operator = new Coalesce($a, $b);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testEmptyCoalesceThrows()
    {
        $this->expectException(SparQlException::class);
        new Coalesce();
    }
}
