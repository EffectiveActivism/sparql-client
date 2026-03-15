<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Sha512;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class Sha512Test extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'SHA512(?subject)';

    public function testOperator()
    {
        $term = new Variable('subject');
        $operator = new Sha512($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
