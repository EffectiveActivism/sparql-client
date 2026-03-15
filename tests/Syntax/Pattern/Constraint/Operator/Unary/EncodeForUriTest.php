<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\EncodeForUri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EncodeForUriTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'ENCODE_FOR_URI(?subject)';

    public function testOperator()
    {
        $term = new Variable('subject');
        $operator = new EncodeForUri($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testGetExpression()
    {
        $term = new Variable('subject');
        $operator = new EncodeForUri($term);
        $this->assertEquals($term, $operator->getExpression());
    }
}
