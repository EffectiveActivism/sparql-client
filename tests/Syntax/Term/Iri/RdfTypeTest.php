<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Iri;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\RdfType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RdfTypeTest extends KernelTestCase
{
    const SERIALIZED_VALUE = 'a';

    const RAW_VALUE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';

    public function testRdfType()
    {
        $rdfType = new RdfType();
        $this->assertEquals(self::SERIALIZED_VALUE, $rdfType->serialize());
        $this->assertEquals(self::RAW_VALUE, $rdfType->getRawValue());
    }
}
