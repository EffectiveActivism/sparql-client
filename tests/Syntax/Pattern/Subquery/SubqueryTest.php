<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Subquery;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Subquery\Subquery;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubqueryTest extends KernelTestCase
{
    const SERIALIZED_VALUE = '{ SELECT ?subject WHERE { ?subject <http://schema.org/headline> """Lorem""" . } }';

    public function testSubquery()
    {
        $subject = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral('Lorem');
        $triple = new Triple($subject, $predicate, $object);
        $statement = new SelectStatement([$subject]);
        $statement->where([$triple]);
        $subquery = new Subquery($statement);
        $this->assertEquals(self::SERIALIZED_VALUE, $subquery->serialize());
        $this->assertEquals([$subject, $predicate, $object], $subquery->getTerms());
        $this->assertEquals([$subject, $predicate, $object], $subquery->toArray());
    }
}
