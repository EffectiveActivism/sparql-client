<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\InversePath;
use EffectiveActivism\SparQlClient\Syntax\Term\Set\NegatedPropertySet;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NegatedPropertySetTest extends KernelTestCase
{
    public function testZeroOrOnePath()
    {
        $predicate = new Iri('http://schema.org/headline');
        $negatedPropertySet = new NegatedPropertySet([$predicate]);
        $this->assertEquals('!<http://schema.org/headline>', $negatedPropertySet->serialize());
        $inversePredicate = new InversePath($predicate);
        $negatedPropertySet = new NegatedPropertySet([$inversePredicate]);
        $this->assertEquals('!(^<http://schema.org/headline>)', $negatedPropertySet->serialize());
    }
}
