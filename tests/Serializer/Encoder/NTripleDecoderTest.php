<?php

namespace EffectiveActivism\SparQlClient\Tests\Serializer\Decoder;

use EffectiveActivism\SparQlClient\Serializer\Encoder\NTripleDecoder;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class NTripleDecoderTest extends KernelTestCase
{
    protected SerializerInterface $serializer;

    public function setUp(): void
    {
        $encoders = [new NTripleDecoder()];
        $this->serializer = new Serializer([], $encoders);
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Encoder\NTripleDecoder
     */
    public function testDecoder()
    {
        $data = file_get_contents(__DIR__ . '/../../fixtures/shacl-validation-request.ntriple');
        $decodedData = $this->serializer->decode($data, NTripleDecoder::FORMAT);
        $this->assertCount(2, $decodedData);
        $triple = array_shift($decodedData);
        $this->assertInstanceOf(TripleInterface::class, $triple);
        // Test all term types.
        $data = file_get_contents(__DIR__ . '/../../fixtures/decoder-request-types.ntriple');
        $decodedData = $this->serializer->decode($data, NTripleDecoder::FORMAT);
        $this->assertCount(4, $decodedData);
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Serializer\Encoder\NTripleDecoder
     */
    public function testDecoderExceptions()
    {
        $data = file_get_contents(__DIR__ . '/../../fixtures/decoder-bad-request.ntriple');
        $threwException = false;
        try {
            $this->serializer->decode($data, NTripleDecoder::FORMAT);
        } catch (InvalidArgumentException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        $data = file_get_contents(__DIR__ . '/../../fixtures/decoder-bad-type.ntriple');
        $threwException = false;
        try {
            $this->serializer->decode($data, NTripleDecoder::FORMAT);
        } catch (InvalidArgumentException $exception) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
