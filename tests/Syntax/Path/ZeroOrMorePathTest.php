<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\ZeroOrMorePath;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ZeroOrMorePathTest extends KernelTestCase
{
    public function testZeroOrMorePath()
    {
        $predicate = new Iri('http://schema.org/headline');
        $zeroOrMorePredicate = new ZeroOrMorePath($predicate);
        $this->assertEquals('<http://schema.org/headline>*', $zeroOrMorePredicate->serialize());
        $doubleZeroOrMorePredicate = new ZeroOrMorePath($zeroOrMorePredicate);
        $this->assertEquals('(<http://schema.org/headline>*)*', $doubleZeroOrMorePredicate->serialize());
    }
}
