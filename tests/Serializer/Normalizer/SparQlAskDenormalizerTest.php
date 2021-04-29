<?php

namespace EffectiveActivism\SparQlClient\Tests\Serializer\Normalizer;

use EffectiveActivism\SparQlClient\Exception\InvalidResultException;
use EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlAskDenormalizer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SparQlAskDenormalizerTest extends KernelTestCase
{
    protected SerializerInterface $serializer;

    public function setUp(): void
    {
        $normalizers = [new SparQlAskDenormalizer()];
        $encoders = [new XmlEncoder()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlAskDenormalizer
     */
    public function testDenoarmlizer()
    {
        $data = file_get_contents(__DIR__ . '/../../fixtures/normalizer-ask-true.xml');
        $denormalizedData = $this->serializer->deserialize($data, SparQlAskDenormalizer::TYPE, 'xml');
        $this->assertTrue($denormalizedData);
        $data = file_get_contents(__DIR__ . '/../../fixtures/normalizer-ask-false.xml');
        $denormalizedData = $this->serializer->deserialize($data, SparQlAskDenormalizer::TYPE, 'xml');
        $this->assertFalse($denormalizedData);
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Normalizer\SparQlAskDenormalizer
     */
    public function testDenoarmlizerExceptions()
    {
        $data = file_get_contents(__DIR__ . '/../../fixtures/normalizer-ask-bad-response.xml');
        $this->expectException(InvalidResultException::class);
        $denormalizedData = $this->serializer->deserialize($data, SparQlAskDenormalizer::TYPE, 'xml');
    }
}
