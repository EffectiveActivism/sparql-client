<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Iri;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;

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
            } catch (InvalidArgumentException $exception) {
                $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            }
        }
        foreach (self::INVALID_LOCAL_PART as $prefix => $localPart) {
            try {
                new PrefixedIri($prefix, $localPart);
                $this->assertFalse(true);
            } catch (InvalidArgumentException $exception) {
                $this->assertInstanceOf(InvalidArgumentException::class, $exception);
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
}
