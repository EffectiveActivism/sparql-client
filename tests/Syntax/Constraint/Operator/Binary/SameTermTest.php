<?php

namespace EffectiveActivism\SparQlClient\Tests\Constraint;

use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Binary\SameTerm;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SameTermTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'sameTERM("lorem","ipsum")';

    public function testOperator()
    {
        $term1 = new PlainLiteral('lorem');
        $term2 = new PlainLiteral('ipsum');
        $operator = new SameTerm($term1, $term2);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
