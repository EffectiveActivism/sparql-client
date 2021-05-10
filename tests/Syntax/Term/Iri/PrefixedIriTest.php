<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Iri;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PrefixedIriTest extends KernelTestCase
{
    const INVALID_PREFIX = [
        '‿schema' => 'headline',
        'http://schema.org/' => 'headline',
    ];

    const INVALID_LOCAL_PART = [
        'schema' => '‿headline',
        'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
    ];

    const VALID_PREFIXED_IRIS = [
        'schema' => 'headline',
        'rdf' => 'type',
    ];

    public function testInvalidPrefixAndLocalPart()
    {
        foreach (self::INVALID_PREFIX as $prefix => $localPart) {
            try {
                new PrefixedIri($prefix, $localPart);
                $this->assertFalse(true);
            } catch (SparQlException $exception) {
                $this->assertInstanceOf(SparQlException::class, $exception);
            }
        }
        foreach (self::INVALID_LOCAL_PART as $prefix => $localPart) {
            try {
                new PrefixedIri($prefix, $localPart);
                $this->assertFalse(true);
            } catch (SparQlException $exception) {
                $this->assertInstanceOf(SparQlException::class, $exception);
            }
        }
    }

    public function testValidPrefixAndLocalPart()
    {
        foreach (self::VALID_PREFIXED_IRIS as $prefix => $localPart) {
            $this->assertInstanceOf(PrefixedIri::class, new PrefixedIri($prefix, $localPart));
        }
    }

    public function testGetPrefixAndLocalPart()
    {
        $prefix = new PrefixedIri('lorem', 'ipsum');
        $this->assertEquals('lorem', $prefix->getPrefix());
        $this->assertEquals('ipsum', $prefix->getLocalPart());
    }

    public function testRawValue()
    {
        $prefix = new PrefixedIri('lorem', 'ipsum');
        $this->assertEquals(sprintf('%s:%s', 'lorem', 'ipsum'), $prefix->getRawValue());
    }

    public function testSerialize()
    {
        $prefix = new PrefixedIri('lorem', 'ipsum');
        $this->assertEquals(sprintf('%s:%s', 'lorem', 'ipsum'), $prefix->serialize());
    }
}
