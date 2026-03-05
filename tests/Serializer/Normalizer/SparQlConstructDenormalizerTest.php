<?php

namespace EffectiveActivism\SparQlClient\Tests\Serializer\Normalizer;

use EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlConstructDenormalizer;
use EffectiveActivism\SparQlClient\Syntax\Term\BlankNode\BlankNode;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SparQlConstructDenormalizerTest extends KernelTestCase
{
    protected SerializerInterface $serializer;

    public function setUp(): void
    {
        $normalizers = [new SparQlConstructDenormalizer()];
        $encoders = [new XmlEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlConstructDenormalizer
     */
    public function testPlainLiteralAndIri()
    {
        $data = file_get_contents(__DIR__ . '/../../fixtures/normalizer-construct-plain-iri.xml');
        $result = $this->serializer->deserialize($data, SparQlConstructDenormalizer::TYPE, 'xml');
        $this->assertCount(2, $result);
        [$subject, $predicate, $object] = $result[0];
        $this->assertInstanceOf(Iri::class, $subject);
        $this->assertEquals('<urn:uuid:d8c0c240-17a2-421e-8c24-49e75a1bddf0>', $subject->serialize());
        $this->assertInstanceOf(PrefixedIri::class, $predicate);
        $this->assertEquals('schema:name', $predicate->serialize());
        $this->assertInstanceOf(PlainLiteral::class, $object);
        $this->assertEquals('"""Lorem"""', $object->serialize());
        [$subject, $predicate, $object] = $result[1];
        $this->assertInstanceOf(Iri::class, $subject);
        $this->assertInstanceOf(PrefixedIri::class, $predicate);
        $this->assertEquals('schema:knows', $predicate->serialize());
        $this->assertInstanceOf(Iri::class, $object);
        $this->assertEquals('<urn:uuid:3850ff8f-dbaa-4b11-80d4-43b22fd18855>', $object->serialize());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlConstructDenormalizer
     */
    public function testTypedLiteralUrlDatatype()
    {
        $data = file_get_contents(__DIR__ . '/../../fixtures/normalizer-construct-typed-literal-url.xml');
        $result = $this->serializer->deserialize($data, SparQlConstructDenormalizer::TYPE, 'xml');
        $this->assertCount(1, $result);
        [$subject, $predicate, $object] = $result[0];
        $this->assertInstanceOf(Iri::class, $subject);
        $this->assertInstanceOf(PrefixedIri::class, $predicate);
        $this->assertEquals('schema:age', $predicate->serialize());
        $this->assertInstanceOf(TypedLiteral::class, $object);
        $this->assertEquals('"""42"""^^<http://www.w3.org/2001/XMLSchema#integer>', $object->serialize());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlConstructDenormalizer
     */
    public function testTypedLiteralPrefixedDatatype()
    {
        $data = file_get_contents(__DIR__ . '/../../fixtures/normalizer-construct-typed-literal-prefixed.xml');
        $result = $this->serializer->deserialize($data, SparQlConstructDenormalizer::TYPE, 'xml');
        $this->assertCount(1, $result);
        [$subject, $predicate, $object] = $result[0];
        $this->assertInstanceOf(Iri::class, $subject);
        $this->assertInstanceOf(PrefixedIri::class, $predicate);
        $this->assertInstanceOf(TypedLiteral::class, $object);
        $this->assertEquals('"""42"""^^xsd:integer', $object->serialize());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlConstructDenormalizer
     */
    public function testBlankNode()
    {
        $data = file_get_contents(__DIR__ . '/../../fixtures/normalizer-construct-blank-node.xml');
        $result = $this->serializer->deserialize($data, SparQlConstructDenormalizer::TYPE, 'xml');
        $this->assertCount(1, $result);
        [$subject, $predicate, $object] = $result[0];
        $this->assertInstanceOf(Iri::class, $subject);
        $this->assertInstanceOf(PrefixedIri::class, $predicate);
        $this->assertInstanceOf(BlankNode::class, $object);
        $this->assertEquals('_:b1', $object->serialize());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlConstructDenormalizer
     */
    public function testMultipleDescriptions()
    {
        $data = file_get_contents(__DIR__ . '/../../fixtures/normalizer-construct-multiple.xml');
        $result = $this->serializer->deserialize($data, SparQlConstructDenormalizer::TYPE, 'xml');
        $this->assertCount(2, $result);
        [$subject, $predicate, $object] = $result[0];
        $this->assertInstanceOf(Iri::class, $subject);
        $this->assertEquals('<urn:uuid:d8c0c240-17a2-421e-8c24-49e75a1bddf0>', $subject->serialize());
        $this->assertInstanceOf(PlainLiteral::class, $object);
        $this->assertEquals('"""Lorem"""', $object->serialize());
        [$subject, $predicate, $object] = $result[1];
        $this->assertInstanceOf(Iri::class, $subject);
        $this->assertEquals('<urn:uuid:3850ff8f-dbaa-4b11-80d4-43b22fd18855>', $subject->serialize());
        $this->assertInstanceOf(PlainLiteral::class, $object);
        $this->assertEquals('"""Ipsum"""', $object->serialize());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlConstructDenormalizer
     */
    public function testUnhandledValueTypeIsSkipped()
    {
        $normalizer = new SparQlConstructDenormalizer();
        $data = [
            '@xmlns:schema' => 'https://schema.org/',
            'rdf:Description' => [
                '@rdf:about' => 'urn:uuid:d8c0c240-17a2-421e-8c24-49e75a1bddf0',
                'schema:unknown' => ['@someUnknownKey' => 'value'],
            ],
        ];
        $result = $normalizer->denormalize($data, SparQlConstructDenormalizer::TYPE);
        $this->assertCount(0, $result);
    }
}
