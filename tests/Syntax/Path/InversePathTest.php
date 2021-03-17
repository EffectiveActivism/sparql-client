<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\InversePath;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InversePathTest extends KernelTestCase
{
    public function testInversePath()
    {
        $predicate = new Iri('http://schema.org/headline');
        $inversePredicate = new InversePath($predicate);
        $this->assertEquals('^<http://schema.org/headline>', $inversePredicate->serialize());
        $doubleInversePredicate = new InversePath($inversePredicate);
        $this->assertEquals('^(^<http://schema.org/headline>)', $doubleInversePredicate->serialize());
    }
}
