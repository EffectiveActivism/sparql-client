<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\TriplesNode;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\TriplesNode\BlankNodePropertyList;
use EffectiveActivism\SparQlClient\Syntax\Term\TriplesNode\Collection;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TriplesNodeIntegrationTest extends KernelTestCase
{
    public function testCollectionInObjectAndSubjectPosition()
    {
        $hasList = new PrefixedIri('ex', 'hasList');
        $object = new Collection([new Variable('a'), new Variable('b')]);
        $triple = new Triple(new Variable('x'), $hasList, $object);
        $this->assertEquals('?x ex:hasList ( ?a ?b )', $triple->serialize());

        $subject = new Collection([new Variable('a'), new Variable('b')]);
        $triple = new Triple($subject, new PrefixedIri('ex', 'p'), new Variable('z'));
        $this->assertEquals('( ?a ?b ) ex:p ?z', $triple->serialize());
    }

    public function testBlankNodePropertyListInObjectAndSubjectPosition()
    {
        $list = new BlankNodePropertyList([
            [new PrefixedIri('ex', 'q'), new Variable('y')],
            [new PrefixedIri('ex', 'r'), new Variable('z')],
        ]);
        $triple = new Triple(new Variable('x'), new PrefixedIri('ex', 'p'), $list);
        $this->assertEquals('?x ex:p [ ex:q ?y ; ex:r ?z ]', $triple->serialize());

        $subject = new BlankNodePropertyList([[new PrefixedIri('ex', 'a'), new Variable('b')]]);
        $triple = new Triple($subject, new PrefixedIri('ex', 'c'), new Variable('d'));
        $this->assertEquals('[ ex:a ?b ] ex:c ?d', $triple->serialize());
    }

    public function testTriplesNodeRejectedInPredicatePosition()
    {
        $collection = new Collection([new Variable('a')]);
        $threwException = false;
        try {
            new Triple(new Variable('x'), $collection, new Variable('z'));
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);

        $list = new BlankNodePropertyList([[new PrefixedIri('ex', 'q'), new Variable('y')]]);
        $threwException = false;
        try {
            (new Triple(new Variable('x'), new PrefixedIri('ex', 'p'), new Variable('z')))->setPredicate($list);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }

    public function testSelectStatementWithTriplesNodes()
    {
        $statement = new SelectStatement([new Variable('x')]);
        $statement->withNamespaces(['ex' => 'http://example.org/']);
        $statement->where([
            new Triple(
                new Variable('x'),
                new PrefixedIri('ex', 'hasList'),
                new Collection([new Variable('a'), new Variable('b')])
            ),
            new Triple(
                new BlankNodePropertyList([[new PrefixedIri('ex', 'q'), new Variable('y')]]),
                new PrefixedIri('ex', 'p'),
                new Variable('x')
            ),
        ]);
        $expected = 'PREFIX ex: <http://example.org/> '
            . 'SELECT ?x WHERE { ?x ex:hasList ( ?a ?b ) . [ ex:q ?y ] ex:p ?x . }';
        $this->assertEquals($expected, $statement->toQuery());
    }
}
