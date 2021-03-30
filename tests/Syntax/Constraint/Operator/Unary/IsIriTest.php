<?php

namespace EffectiveActivism\SparQlClient\Tests\Constraint;

use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Unary\IsIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IsIriTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'IsIRI("lorem")';

    public function testOperator()
    {
        $term = new PlainLiteral('lorem');
        $operator = new IsIri($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
