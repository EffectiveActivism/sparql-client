<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Literal;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractLiteralTest extends KernelTestCase
{
    public function testCoercedSerialize()
    {
        $literal = new PlainLiteral('Lorem');
        $this->assertEquals('"Lorem"^^rdf:HTML', $literal->typeCoercedSerialize(new PrefixedIri('rdf', 'HTML')));
        $literal = new PlainLiteral('Lorem', 'la');
        $this->assertEquals('"Lorem"^^rdf:HTML', $literal->typeCoercedSerialize(new PrefixedIri('rdf', 'HTML')));
        $literal = new TypedLiteral(true);
        $this->assertEquals('"true"^^rdf:HTML', $literal->typeCoercedSerialize(new PrefixedIri('rdf', 'HTML')));
    }
}
