<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Sha384;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class Sha384Test extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'SHA384(?subject)';

    public function testOperator()
    {
        $term = new Variable('subject');
        $operator = new Sha384($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testGetExpression()
    {
        $term = new Variable('subject');
        $operator = new Sha384($term);
        $this->assertEquals($term, $operator->getExpression());
    }
}
