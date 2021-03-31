<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\AbstractBinaryPath;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractBinaryPathTest extends KernelTestCase
{
    const IRI_1 = 'http://schema.org/headline';
    const IRI_2 = 'http://schema.org/identifier';
    const IRI_3 = 'http://schema.org/description';
    const IRI_4 = 'http://schema.org/text';

    public function testAbstractPath()
    {
        $predicate1 = new Iri(self::IRI_1);
        $predicate2 = new Iri(self::IRI_2);
        $class = new class($predicate1, $predicate2) extends AbstractBinaryPath {

            public function serialize(): string
            {
                return '';
            }
        };
        $this->assertEquals(self::IRI_1, $class->getRawValue());
        $this->assertEquals($predicate1, $class->getTerm1());
        $this->assertEquals(null, $class->getVariableName());
        $class->setVariableName('predicate');
        $this->assertEquals('predicate', $predicate1->getVariableName());
        $predicate = new Iri(self::IRI_3);
        $class->setTerm1($predicate);
        $this->assertEquals($predicate, $class->getTerm1());
        $predicate = new Iri(self::IRI_4);
        $class->setTerm2($predicate);
        $this->assertEquals($predicate, $class->getTerm2());
    }
}
