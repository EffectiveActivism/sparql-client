<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Statement\DropStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DropStatementTest extends KernelTestCase
{
    const GRAPH_URI = 'http://example.org/g';

    const DROP_STATEMENT = 'DROP GRAPH <http://example.org/g>';

    const DROP_SILENT_STATEMENT = 'DROP SILENT GRAPH <http://example.org/g>';

    public function testDropStatement()
    {
        $graph = new Iri(self::GRAPH_URI);
        $statement = new DropStatement($graph);
        $this->assertEquals(self::DROP_STATEMENT, $statement->toQuery());
        $this->assertEquals($graph, $statement->getGraph());
    }

    public function testDropSilentStatement()
    {
        $graph = new Iri(self::GRAPH_URI);
        $statement = (new DropStatement($graph))->silent();
        $this->assertEquals(self::DROP_SILENT_STATEMENT, $statement->toQuery());
    }

    public function testDropStatementUndefinedPrefix()
    {
        $threwException = false;
        try {
            $statement = new DropStatement(new PrefixedIri('ex', 'g'));
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
