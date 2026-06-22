<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\TriplesNode;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\BlankNode\BlankNode;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\RdfType;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\InversePath;
use EffectiveActivism\SparQlClient\Syntax\Term\TriplesNode\BlankNodePropertyList;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BlankNodePropertyListTest extends KernelTestCase
{
    const IRI_Q = 'http://schema.org/q';

    const IRI_R = 'http://schema.org/r';

    public function testBlankNodePropertyList()
    {
        $predicateQ = new Iri(self::IRI_Q);
        $objectY = new Variable('y');
        $predicateR = new Iri(self::IRI_R);
        $objectZ = new Variable('z');
        $list = new BlankNodePropertyList([[$predicateQ, $objectY], [$predicateR, $objectZ]]);
        $this->assertEquals(sprintf('[ <%s> ?y ; <%s> ?z ]', self::IRI_Q, self::IRI_R), $list->serialize());
        $this->assertEquals(sprintf('[ <%s> ?y ; <%s> ?z ]', self::IRI_Q, self::IRI_R), $list->getRawValue());
        $this->assertEquals([$predicateQ, $objectY, $predicateR, $objectZ], $list->getTerms());
        $this->assertEquals([[$predicateQ, $objectY], [$predicateR, $objectZ]], $list->getPairs());
        $list->setVariableName('node');
        $this->assertEquals('node', $list->getVariableName());
        $result = $list->setPairs([[$predicateQ, $objectY]]);
        $this->assertInstanceOf(BlankNodePropertyList::class, $result);
        $this->assertEquals(sprintf('[ <%s> ?y ]', self::IRI_Q), $list->serialize());
    }

    public function testRdfTypePredicateIsAbbreviated()
    {
        $list = new BlankNodePropertyList([[new RdfType(), new Iri(self::IRI_Q)]]);
        $this->assertEquals(sprintf('[ a <%s> ]', self::IRI_Q), $list->serialize());
    }

    public function testBlankNodePropertyListExceptions()
    {
        $predicate = new Iri(self::IRI_Q);
        $object = new Variable('y');
        // Empty lists are not permitted by the grammar.
        $this->assertExceptionThrown(fn () => new BlankNodePropertyList([]));
        // Pairs must contain exactly two elements.
        $this->assertExceptionThrown(fn () => new BlankNodePropertyList([[$predicate]]));
        $this->assertExceptionThrown(fn () => new BlankNodePropertyList([[$predicate, $object, $object]]));
        // Both members of a pair must be terms.
        $this->assertExceptionThrown(fn () => new BlankNodePropertyList([['not-a-term', $object]]));
        // A predicate cannot be a literal, a blank node, or another triples node.
        $this->assertExceptionThrown(fn () => new BlankNodePropertyList([[new PlainLiteral('lorem'), $object]]));
        $this->assertExceptionThrown(fn () => new BlankNodePropertyList([[new BlankNode('label'), $object]]));
        $this->assertExceptionThrown(fn () => new BlankNodePropertyList([[new BlankNodePropertyList([[$predicate, $object]]), $object]]));
        // An object cannot be a predicate-only construct.
        $this->assertExceptionThrown(fn () => new BlankNodePropertyList([[$predicate, new InversePath($predicate)]]));
        // setPairs() preserves the invariants.
        $this->assertExceptionThrown(fn () => (new BlankNodePropertyList([[$predicate, $object]]))->setPairs([]));
    }

    private function assertExceptionThrown(callable $callback): void
    {
        $threwException = false;
        try {
            $callback();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
