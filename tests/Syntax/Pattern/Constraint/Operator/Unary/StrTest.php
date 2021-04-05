<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Str;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StrTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'STR("lorem")';

    public function testOperator()
    {
        $term = new PlainLiteral('lorem');
        $operator = new Str($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
