<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Variadic;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Variadic\Concat;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConcatTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'CONCAT(?first, ?last)';

    public function testOperator()
    {
        $first = new Variable('first');
        $last = new Variable('last');
        $operator = new Concat($first, $last);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
