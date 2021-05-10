<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Iri;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IriTest extends KernelTestCase
{
    const INVALID_IRIS = [
        'http:///schema.org/headline',
        'aaa:uuid:81cef122-8240-11eb-b727-b7e6b74f5291',
    ];

    const VALID_IRIS = [
        'http://schema.org/headline',
        'urn:uuid:81cef122-8240-11eb-b727-b7e6b74f5291',
    ];

    const IRI = 'http://schema.org/headline';

    public function testInvalidIri()
    {
        foreach (self::INVALID_IRIS as $invalidIri) {
            try {
                new Iri($invalidIri);
                $this->assertFalse(true);
            } catch (SparQlException $exception) {
                $this->assertInstanceOf(SparQlException::class, $exception);
            }
        }
    }

    public function testValidIri()
    {
        foreach (self::VALID_IRIS as $validIri) {
            $this->assertInstanceOf(Iri::class, new Iri($validIri));
        }
    }

    public function testSerializedIri()
    {
        $iri = new Iri(self::IRI);
        $this->assertEquals(sprintf('<%s>', self::IRI), $iri->serialize());
    }

    public function testRawValue()
    {
        $iri = new Iri(self::IRI);
        $this->assertEquals(self::IRI, $iri->getRawValue());
    }
}
