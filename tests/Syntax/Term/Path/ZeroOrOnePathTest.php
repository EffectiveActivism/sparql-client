<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\ZeroOrOnePath;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ZeroOrOnePathTest extends KernelTestCase
{
    public function testZeroOrOnePath()
    {
        $predicate = new Iri('http://schema.org/headline');
        $zeroOrOnePredicate = new ZeroOrOnePath($predicate);
        $this->assertEquals('<http://schema.org/headline>?', $zeroOrOnePredicate->serialize());
        $doubleZeroOrOnePredicate = new ZeroOrOnePath($zeroOrOnePredicate);
        $this->assertEquals('(<http://schema.org/headline>?)?', $doubleZeroOrOnePredicate->serialize());
    }
}
