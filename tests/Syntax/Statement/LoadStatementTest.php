<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Statement\LoadStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LoadStatementTest extends KernelTestCase
{
    const SOURCE_URI = 'http://example.org/data';
    const GRAPH_URI = 'http://example.org/g';

    const LOAD_STATEMENT = 'LOAD <http://example.org/data>';
    const LOAD_SILENT_STATEMENT = 'LOAD SILENT <http://example.org/data>';
    const LOAD_INTO_GRAPH_STATEMENT = 'LOAD <http://example.org/data> INTO GRAPH <http://example.org/g>';
    const LOAD_SILENT_INTO_GRAPH_STATEMENT = 'LOAD SILENT <http://example.org/data> INTO GRAPH <http://example.org/g>';

    public function testLoadStatement()
    {
        $source = new Iri(self::SOURCE_URI);
        $statement = new LoadStatement($source);
        $this->assertEquals(self::LOAD_STATEMENT, $statement->toQuery());
        $this->assertEquals($source, $statement->getSource());
        $this->assertNull($statement->getGraph());
    }

    public function testLoadSilentStatement()
    {
        $source = new Iri(self::SOURCE_URI);
        $statement = (new LoadStatement($source))->silent();
        $this->assertEquals(self::LOAD_SILENT_STATEMENT, $statement->toQuery());
    }

    public function testLoadIntoGraphStatement()
    {
        $source = new Iri(self::SOURCE_URI);
        $graph = new Iri(self::GRAPH_URI);
        $statement = (new LoadStatement($source))->into($graph);
        $this->assertEquals(self::LOAD_INTO_GRAPH_STATEMENT, $statement->toQuery());
        $this->assertEquals($graph, $statement->getGraph());
    }

    public function testLoadSilentIntoGraphStatement()
    {
        $source = new Iri(self::SOURCE_URI);
        $graph = new Iri(self::GRAPH_URI);
        $statement = (new LoadStatement($source))->silent()->into($graph);
        $this->assertEquals(self::LOAD_SILENT_INTO_GRAPH_STATEMENT, $statement->toQuery());
    }

    public function testLoadStatementUndefinedSourcePrefix()
    {
        $threwException = false;
        try {
            $statement = new LoadStatement(new PrefixedIri('ex', 'data'));
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }

    public function testLoadStatementUndefinedGraphPrefix()
    {
        $threwException = false;
        try {
            $statement = (new LoadStatement(new Iri(self::SOURCE_URI)))->into(new PrefixedIri('ex', 'g'));
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
