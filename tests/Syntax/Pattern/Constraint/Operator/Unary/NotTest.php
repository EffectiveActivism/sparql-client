<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Not;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NotTest extends KernelTestCase
{
    const SERIALIZED_OPERATOR = '! """lorem"""';

    const SERIALIZED_OPERATOR_VARIABLE = '! ?subject';

    const SERIALIZED_OPERATOR_IRI = '! <urn:uuid:a8ea08b2-95f7-11eb-aa9c-23071bcbf225>';

    public function testOperator()
    {
        $term = new PlainLiteral('lorem');
        $operator = new Not($term);
        $this->assertEquals(self::SERIALIZED_OPERATOR, $operator->serialize());
    }

    public function testOperatorWithVariable()
    {
        $variable = new Variable('subject');
        $operator = new Not($variable);
        $this->assertEquals(self::SERIALIZED_OPERATOR_VARIABLE, $operator->serialize());
    }

    public function testOperatorWithIri()
    {
        $iri = new Iri('urn:uuid:a8ea08b2-95f7-11eb-aa9c-23071bcbf225');
        $operator = new Not($iri);
        $this->assertEquals(self::SERIALIZED_OPERATOR_IRI, $operator->serialize());
    }

    public function testGetExpression()
    {
        $term = new PlainLiteral('lorem');
        $operator = new Not($term);
        $this->assertEquals($term, $operator->getExpression());
    }
}
