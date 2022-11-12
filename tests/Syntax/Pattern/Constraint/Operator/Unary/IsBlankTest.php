<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\IsBlank;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IsBlankTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'IsBLANK("""lorem""")';

    public function testOperator()
    {
        $term = new PlainLiteral('lorem');
        $operator = new IsBlank($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
