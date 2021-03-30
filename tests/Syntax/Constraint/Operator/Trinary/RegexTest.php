<?php

namespace EffectiveActivism\SparQlClient\Tests\Constraint;

use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Trinary\Regex;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RegexTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'REGEX("lorem","/ipsum/")';

    const SERIALIZED_OPERATOR_WITH_FLAGS = 'REGEX("lorem","/ipsum/","foo")';

    public function testOperator()
    {
        $term1 = new PlainLiteral('lorem');
        $term2 = new PlainLiteral('/ipsum/');
        $term3 = new PlainLiteral('foo');
        $operator = new Regex($term1, $term2);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
        $operator = new Regex($term1, $term2, $term3);
        $this->assertEquals(self::SERIALIZED_OPERATOR_WITH_FLAGS, $operator->serialize());
    }
}
