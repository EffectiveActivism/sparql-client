<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\AbstractUnaryPath;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractUnaryPathTest extends KernelTestCase
{
    const IRI = 'http://schema.org/headline';

    const IRI_2 = 'http://schema.org/identifier';

    public function testAbstractPath()
    {
        $predicate = new Iri(self::IRI);
        $class = new class($predicate) extends AbstractUnaryPath {

            public function serialize(): string
            {
                return '';
            }
        };
        $this->assertEquals(self::IRI, $class->getRawValue());
        $this->assertEquals($predicate, $class->getTerm());
        $this->assertEquals(null, $class->getVariableName());
        $class->setVariableName('predicate');
        $this->assertEquals('predicate', $predicate->getVariableName());
        $predicate = new Iri(self::IRI_2);
        $class->setTerm($predicate);
        $this->assertEquals($predicate, $class->getTerm());
    }
}
