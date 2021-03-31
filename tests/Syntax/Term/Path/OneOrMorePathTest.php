<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\OneOrMorePath;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OneOrMorePathTest extends KernelTestCase
{
    public function testInversePath()
    {
        $predicate = new Iri('http://schema.org/headline');
        $oneOrMorePredicate = new OneOrMorePath($predicate);
        $this->assertEquals('<http://schema.org/headline>+', $oneOrMorePredicate->serialize());
        $doubleOneOrMorePredicate = new OneOrMorePath($oneOrMorePredicate);
        $this->assertEquals('(<http://schema.org/headline>+)+', $doubleOneOrMorePredicate->serialize());
    }
}
