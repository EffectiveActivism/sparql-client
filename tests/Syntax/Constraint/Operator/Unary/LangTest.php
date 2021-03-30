<?php

namespace EffectiveActivism\SparQlClient\Tests\Constraint;

use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Unary\Lang;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LangTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'LANG("lorem")';

    public function testOperator()
    {
        $term = new PlainLiteral('lorem');
        $operator = new Lang($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
