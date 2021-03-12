<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Triple\Triple;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TripleTest extends KernelTestCase
{
    const SUBJECT_URI = 'urn:uuid:d8c03876-823e-11eb-b11c-b339931559e6';

    public function testTriple()
    {
        $subject = new Iri(self::SUBJECT_URI);
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subject, $predicate, $object);
        $this->assertEquals($triple->toArray(), [$subject, $predicate, $object]);
        $triple->setSubject($subject);
        $triple->setPredicate($predicate);
        $triple->setObject($object);
        $this->assertEquals($triple->getSubject(), $subject);
        $this->assertEquals($triple->getPredicate(), $predicate);
        $this->assertEquals($triple->getObject(), $object);
    }
}
