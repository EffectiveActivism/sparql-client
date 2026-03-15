<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Statement\CreateStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CreateStatementTest extends KernelTestCase
{
    const GRAPH_URI = 'http://example.org/g';

    const CREATE_STATEMENT = 'CREATE GRAPH <http://example.org/g>';

    const CREATE_SILENT_STATEMENT = 'CREATE SILENT GRAPH <http://example.org/g>';

    public function testCreateStatement()
    {
        $graph = new Iri(self::GRAPH_URI);
        $statement = new CreateStatement($graph);
        $this->assertEquals(self::CREATE_STATEMENT, $statement->toQuery());
        $this->assertEquals($graph, $statement->getGraph());
    }

    public function testCreateSilentStatement()
    {
        $graph = new Iri(self::GRAPH_URI);
        $statement = (new CreateStatement($graph))->silent();
        $this->assertEquals(self::CREATE_SILENT_STATEMENT, $statement->toQuery());
    }

    public function testCreateStatementUndefinedPrefix()
    {
        $threwException = false;
        try {
            $statement = new CreateStatement(new PrefixedIri('ex', 'g'));
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
