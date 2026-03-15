<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Sha1;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class Sha1Test extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'SHA1(?subject)';

    public function testOperator()
    {
        $term = new Variable('subject');
        $operator = new Sha1($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
