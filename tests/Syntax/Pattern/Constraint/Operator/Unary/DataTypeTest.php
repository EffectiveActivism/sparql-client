<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Datatype;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DataTypeTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = 'DATATYPE("""lorem""")';

    public function testOperator()
    {
        $term = new PlainLiteral('lorem');
        $operator = new Datatype($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
