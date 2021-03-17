<?php

namespace EffectiveActivism\SparQlClient\Tests\Serializer;

use EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlResultDenormalizer;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class NormalizerTest extends KernelTestCase
{
    protected SerializerInterface $serializer;

    public function setUp(): void
    {
        $normalizers = [new SparQlResultDenormalizer()];
        $encoders = [new XmlEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlResultDenormalizer
     */
    public function testOneResultOneVariable()
    {
        $data = file_get_contents(__DIR__ . '/../fixtures/normalizer-one-result-one-variable.xml');
        $denormalizedData = $this->serializer->deserialize($data, SparQlResultDenormalizer::TYPE, 'xml');
        $this->assertCount(1, $denormalizedData);
        $set = array_shift($denormalizedData);
        $this->assertCount(1, $set);
        /** @var TermInterface $term */
        $term = array_pop($set);
        $this->assertInstanceOf(Iri::class, $term);
        $this->assertEquals('<urn:uuid:a23a3a88-7e86-11eb-b783-9f0a5d690c48>', $term->serialize());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlResultDenormalizer
     */
    public function testTranslatedPlainLiteral()
    {
        $data = file_get_contents(__DIR__ . '/../fixtures/normalizer-translated-plain-literal.xml');
        $denormalizedData = $this->serializer->deserialize($data, SparQlResultDenormalizer::TYPE, 'xml');
        $this->assertCount(1, $denormalizedData);
        $set = array_shift($denormalizedData);
        $this->assertCount(1, $set);
        /** @var TermInterface $term */
        $term = array_pop($set);
        $this->assertInstanceOf(PlainLiteral::class, $term);
        $this->assertEquals('"lorem"@la', $term->serialize());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlResultDenormalizer
     */
    public function testTypedLiteral()
    {
        $data = file_get_contents(__DIR__ . '/../fixtures/normalizer-typed-literal.xml');
        $denormalizedData = $this->serializer->deserialize($data, SparQlResultDenormalizer::TYPE, 'xml');
        $this->assertCount(1, $denormalizedData);
        $set = array_shift($denormalizedData);
        $this->assertCount(2, $set);
        /** @var TermInterface $term */
        $term = array_pop($set);
        $this->assertInstanceOf(TypedLiteral::class, $term);
        $this->assertEquals('"false"^^xsd:boolean', $term->serialize());
        /** @var TermInterface $term */
        $term = array_pop($set);
        $this->assertInstanceOf(TypedLiteral::class, $term);
        $this->assertEquals('"2"^^<http://www.w3.org/2001/XMLSchema#integer>', $term->serialize());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlResultDenormalizer
     */
    public function testOneResultMultipleVariables()
    {
        $data = file_get_contents(__DIR__ . '/../fixtures/normalizer-one-result-multiple-variables.xml');
        $denormalizedData = $this->serializer->deserialize($data, SparQlResultDenormalizer::TYPE, 'xml');
        $this->assertCount(1, $denormalizedData);
        $set = array_shift($denormalizedData);
        $this->assertCount(2, $set);
        /** @var TermInterface $term */
        $term = array_shift($set);
        $this->assertInstanceOf(Iri::class, $term);
        $this->assertEquals('<urn:uuid:99548ea0-7e86-11eb-8087-dbe515dec0d2>', $term->serialize());
        $term = array_shift($set);
        $this->assertInstanceOf(PlainLiteral::class, $term);
        $this->assertEquals('"Lorem"', $term->serialize());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlResultDenormalizer
     */
    public function testMultipleResultsMultipleVariables()
    {
        $data = file_get_contents(__DIR__ . '/../fixtures/normalizer-multiple-results-multiple-variables.xml');
        $denormalizedData = $this->serializer->deserialize($data, SparQlResultDenormalizer::TYPE, 'xml');
        $this->assertCount(2, $denormalizedData);
        $set = array_shift($denormalizedData);
        $this->assertCount(2, $set);
        /** @var TermInterface $term */
        $term = array_shift($set);
        $this->assertInstanceOf(Iri::class, $term);
        $this->assertEquals('<urn:uuid:fcf19bc4-7e81-11eb-a169-175604c7c7bc>', $term->serialize());
        $term = array_shift($set);
        $this->assertInstanceOf(PlainLiteral::class, $term);
        $this->assertEquals('"Lorem"', $term->serialize());
        $set = array_shift($denormalizedData);
        $this->assertCount(2, $set);
        $term = array_shift($set);
        $this->assertInstanceOf(Iri::class, $term);
        $this->assertEquals('<urn:uuid:02aa87b0-7e82-11eb-9e68-bb0651b281cc>', $term->serialize());
        $term = array_shift($set);
        $this->assertInstanceOf(PlainLiteral::class, $term);
        $this->assertEquals('"Ipsum"', $term->serialize());
    }
}
