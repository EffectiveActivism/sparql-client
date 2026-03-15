<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Statement\ClearStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ClearStatementTest extends KernelTestCase
{
    const GRAPH_URI = 'http://example.org/g';

    const CLEAR_STATEMENT = 'CLEAR GRAPH <http://example.org/g>';

    const CLEAR_SILENT_STATEMENT = 'CLEAR SILENT GRAPH <http://example.org/g>';

    public function testClearStatement()
    {
        $graph = new Iri(self::GRAPH_URI);
        $statement = new ClearStatement($graph);
        $this->assertEquals(self::CLEAR_STATEMENT, $statement->toQuery());
        $this->assertEquals($graph, $statement->getGraph());
    }

    public function testClearSilentStatement()
    {
        $graph = new Iri(self::GRAPH_URI);
        $statement = (new ClearStatement($graph))->silent();
        $this->assertEquals(self::CLEAR_SILENT_STATEMENT, $statement->toQuery());
    }
}
