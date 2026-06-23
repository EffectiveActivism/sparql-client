<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Bound;
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

    const SERIALIZED_OPERATOR_OPERATOR = '! (BOUND(?wkt))';

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

    public function testOperatorWithOperator()
    {
        // Negating a built-in call, e.g. "!BOUND(?wkt)". The operand is an
        // operator, so it is parenthesised to preserve precedence.
        $bound = new Bound(new Variable('wkt'));
        $operator = new Not($bound);
        $this->assertEquals(self::SERIALIZED_OPERATOR_OPERATOR, $operator->serialize());
        $this->assertEquals($bound, $operator->getExpression());
    }

    public function testGetExpression()
    {
        $term = new PlainLiteral('lorem');
        $operator = new Not($term);
        $this->assertEquals($term, $operator->getExpression());
    }
}
