<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\TriplesNode;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\InversePath;
use EffectiveActivism\SparQlClient\Syntax\Term\Set\NegatedPropertySet;
use EffectiveActivism\SparQlClient\Syntax\Term\TriplesNode\BlankNodePropertyList;
use EffectiveActivism\SparQlClient\Syntax\Term\TriplesNode\Collection;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CollectionTest extends KernelTestCase
{
    const IRI = 'http://schema.org/headline';

    public function testCollection()
    {
        $iri = new Iri(self::IRI);
        $variable = new Variable('y');
        $literal = new PlainLiteral('lorem');
        $collection = new Collection([$iri, $variable, $literal]);
        $this->assertEquals(sprintf('( <%s> ?y """lorem""" )', self::IRI), $collection->serialize());
        $this->assertEquals(sprintf('( <%s> ?y """lorem""" )', self::IRI), $collection->getRawValue());
        $this->assertEquals([$iri, $variable, $literal], $collection->getMembers());
        $this->assertEquals([$iri, $variable, $literal], $collection->getTerms());
        $collection->setVariableName('list');
        $this->assertEquals('list', $collection->getVariableName());
        $result = $collection->setMembers([$variable]);
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals('( ?y )', $collection->serialize());
    }

    public function testNestedTriplesNodesAreValidMembers()
    {
        $nestedCollection = new Collection([new Variable('a'), new Variable('b')]);
        $nestedList = new BlankNodePropertyList([[new Iri(self::IRI), new Variable('c')]]);
        $collection = new Collection([$nestedCollection, $nestedList]);
        $this->assertEquals(sprintf('( ( ?a ?b ) [ <%s> ?c ] )', self::IRI), $collection->serialize());
    }

    public function testCollectionExceptions()
    {
        // Empty collections are not permitted by the grammar.
        $threwException = false;
        try {
            new Collection([]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Non-term members are rejected.
        $threwException = false;
        try {
            new Collection(['not-a-term']);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Predicate-only constructs are not valid graph nodes.
        $threwException = false;
        try {
            new Collection([new InversePath(new Iri(self::IRI))]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        $threwException = false;
        try {
            new Collection([new NegatedPropertySet([new Iri(self::IRI)])]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // setMembers() preserves the invariants.
        $threwException = false;
        try {
            (new Collection([new Variable('y')]))->setMembers([]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
