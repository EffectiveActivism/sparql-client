<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\AbstractConditionalStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractConditionalStatementTest extends KernelTestCase
{
    const QUERY = 'PREFIX schema: <http://schema.org/> ';

    public function testStatement()
    {
        $subject = new Iri('urn:uuid:ed61d3c8-9203-11eb-9714-83cf7e09838c');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PlainLiteral('Lorem');
        $triple = new Triple($subject, $predicate, $object);
        $class = new class([
            'schema' => 'http://schema.org/',
        ]) extends AbstractConditionalStatement {};
        $class->where([$triple]);
        $this->assertEquals([$triple], $class->getConditions());
    }

    public function testExceptions()
    {
        // Test statement with invalid condition class.
        $predicate = new PrefixedIri('schema', 'headline');
        $class = new class([]) extends AbstractConditionalStatement {};
        $threwException = false;
        try {
            $class->where([$predicate]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with unknown prefix.
        $subject = new Iri('urn:uuid:ed61d3c8-9203-11eb-9714-83cf7e09838c');
        $predicate = new PrefixedIri('unknown', 'headline');
        $object = new PlainLiteral('Lorem');
        $triple = new Triple($subject, $predicate, $object);
        $class = new class([]) extends AbstractConditionalStatement {};
        $threwException = false;
        try {
            $class->where([$triple]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
