<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\LogicalAnd;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LogicalAndTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = '"true"^^xsd:boolean AND "false"^^xsd:boolean';

    public function testOperator()
    {
        $term1 = new PlainLiteral(true);
        $term2 = new PlainLiteral(false);
        $operator = new LogicalAnd($term1, $term2);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }
}
