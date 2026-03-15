<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Timezone;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TimezoneTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'TIMEZONE(?subject)';

    public function testOperator()
    {
        $term = new Variable('subject');
        $operator = new Timezone($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
