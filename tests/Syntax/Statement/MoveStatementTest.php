<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Statement\MoveStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MoveStatementTest extends KernelTestCase
{
    const SRC_URI = 'http://example.org/src';
    const DST_URI = 'http://example.org/dst';

    const MOVE_STATEMENT = 'MOVE GRAPH <http://example.org/src> TO GRAPH <http://example.org/dst>';
    const MOVE_SILENT_STATEMENT = 'MOVE SILENT GRAPH <http://example.org/src> TO GRAPH <http://example.org/dst>';

    public function testMoveStatement()
    {
        $src = new Iri(self::SRC_URI);
        $dst = new Iri(self::DST_URI);
        $statement = new MoveStatement($src, $dst);
        $this->assertEquals(self::MOVE_STATEMENT, $statement->toQuery());
        $this->assertEquals($src, $statement->getSourceGraph());
        $this->assertEquals($dst, $statement->getDestinationGraph());
    }

    public function testMoveSilentStatement()
    {
        $statement = (new MoveStatement(new Iri(self::SRC_URI), new Iri(self::DST_URI)))->silent();
        $this->assertEquals(self::MOVE_SILENT_STATEMENT, $statement->toQuery());
    }

    public function testMoveStatementUndefinedSourcePrefix()
    {
        $threwException = false;
        try {
            $statement = new MoveStatement(new PrefixedIri('ex', 'src'), new Iri(self::DST_URI));
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }

    public function testMoveStatementUndefinedDestinationPrefix()
    {
        $threwException = false;
        try {
            $statement = new MoveStatement(new Iri(self::SRC_URI), new PrefixedIri('ex', 'dst'));
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
