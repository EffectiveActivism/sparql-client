<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Statement\AbstractStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractStatementTest extends KernelTestCase
{
    const QUERY = 'PREFIX schema: <http://schema.org/> ';

    public function testGetNamespaces()
    {
        $class = new class() extends AbstractStatement {};
        $namespaces = ['schema' => 'http://schema.org/'];
        $class->withNamespaces($namespaces);
        $this->assertEquals($namespaces, $class->getNamespaces());
    }

    public function testToQuery()
    {
        $class = new class() extends AbstractStatement {};
        $class->withNamespaces(['schema' => 'http://schema.org/']);
        $this->assertEquals(self::QUERY, $class->toQuery());
    }

    public function testExceptions()
    {
        // Test statement with invalid namespace prefix.
        $threwException = false;
        try {
            $class = new class() extends AbstractStatement {};
            $class->withNamespaces(['!invalid' => 'http://schema.org/']);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with invalid namespace url.
        $threwException = false;
        try {
            $class = new class() extends AbstractStatement {};
            $class->withNamespaces(['schema' => 'invalid_url']);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }

    public function testFromDataset()
    {
        $subject = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new Iri('http://example.org/object');
        $triple = new Triple($subject, $predicate, $object);
        $graphIri = new Iri('http://example.org/mygraph');
        $statement = new SelectStatement([$subject]);
        $statement->where([$triple]);
        $statement->from($graphIri);
        $this->assertStringContainsString('FROM <http://example.org/mygraph>', $statement->toQuery());
        $this->assertStringContainsString('SELECT ?subject FROM <http://example.org/mygraph> WHERE', $statement->toQuery());
    }

    public function testFromNamedDataset()
    {
        $subject = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new Iri('http://example.org/object');
        $triple = new Triple($subject, $predicate, $object);
        $graphIri = new Iri('http://example.org/mygraph');
        $statement = new SelectStatement([$subject]);
        $statement->where([$triple]);
        $statement->fromNamed($graphIri);
        $this->assertStringContainsString('FROM NAMED <http://example.org/mygraph>', $statement->toQuery());
        $this->assertStringContainsString('SELECT ?subject FROM NAMED <http://example.org/mygraph> WHERE', $statement->toQuery());
    }

    public function testWithBase()
    {
        $class = new class() extends AbstractStatement {};
        $class->withBase('http://example.org/');
        $this->assertEquals('BASE <http://example.org/> ', $class->toQuery());
    }

    public function testWithBaseException()
    {
        $threwException = false;
        try {
            $class = new class() extends AbstractStatement {};
            $class->withBase('not_a_url');
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
