<?php

namespace EffectiveActivism\SparQlClient\Tests\Constraint;

use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Unary\IsUri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IsUriTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'IsURI("lorem")';

    public function testOperator()
    {
        $term = new PlainLiteral('lorem');
        $operator = new IsUri($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
