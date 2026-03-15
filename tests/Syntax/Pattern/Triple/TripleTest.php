<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Triple;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\BlankNode\BlankNode;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TripleTest extends KernelTestCase
{
    const SUBJECT_URI = 'urn:uuid:d8c03876-823e-11eb-b11c-b339931559e6';

    const SERIALIZED_TRIPLE = '<urn:uuid:d8c03876-823e-11eb-b11c-b339931559e6> <http://schema.org/headline> """Lorem Ipsum"""';

    public function testTriple()
    {
        $subject = new Iri(self::SUBJECT_URI);
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subject, $predicate, $object);
        $this->assertEquals([$subject, $predicate, $object], $triple->getTerms());
        $this->assertEquals([$subject, $predicate, $object], $triple->toArray());
        $triple->setSubject($subject);
        $triple->setPredicate($predicate);
        $triple->setObject($object);
        $this->assertEquals($triple->getSubject(), $subject);
        $this->assertEquals($triple->getPredicate(), $predicate);
        $this->assertEquals($triple->getObject(), $object);
        $this->assertEquals(self::SERIALIZED_TRIPLE, $triple->serialize());
    }

    public function testTripleExceptions()
    {
        $iri = new Iri(self::SUBJECT_URI);
        $predicate = new Iri('http://schema.org/headline');
        $literal = new PlainLiteral('lorem');
        $blankNode = new BlankNode('b1');
        // Literal as subject.
        $threwException = false;
        try {
            new Triple($literal, $predicate, $iri);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Literal as predicate.
        $threwException = false;
        try {
            new Triple($iri, $literal, $iri);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // BlankNode as predicate.
        $threwException = false;
        try {
            new Triple($iri, $blankNode, $iri);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Literal as subject via setter.
        $threwException = false;
        try {
            (new Triple($iri, $predicate, $iri))->setSubject($literal);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // BlankNode as predicate via setter.
        $threwException = false;
        try {
            (new Triple($iri, $predicate, $iri))->setPredicate($blankNode);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
