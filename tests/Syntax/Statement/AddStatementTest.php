<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Statement\AddStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AddStatementTest extends KernelTestCase
{
    const SRC_URI = 'http://example.org/src';
    const DST_URI = 'http://example.org/dst';

    const ADD_STATEMENT = 'ADD GRAPH <http://example.org/src> TO GRAPH <http://example.org/dst>';
    const ADD_SILENT_STATEMENT = 'ADD SILENT GRAPH <http://example.org/src> TO GRAPH <http://example.org/dst>';

    public function testAddStatement()
    {
        $src = new Iri(self::SRC_URI);
        $dst = new Iri(self::DST_URI);
        $statement = new AddStatement($src, $dst);
        $this->assertEquals(self::ADD_STATEMENT, $statement->toQuery());
        $this->assertEquals($src, $statement->getSourceGraph());
        $this->assertEquals($dst, $statement->getDestinationGraph());
    }

    public function testAddSilentStatement()
    {
        $statement = (new AddStatement(new Iri(self::SRC_URI), new Iri(self::DST_URI)))->silent();
        $this->assertEquals(self::ADD_SILENT_STATEMENT, $statement->toQuery());
    }

    public function testAddStatementUndefinedSourcePrefix()
    {
        $threwException = false;
        try {
            $statement = new AddStatement(new PrefixedIri('ex', 'src'), new Iri(self::DST_URI));
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }

    public function testAddStatementUndefinedDestinationPrefix()
    {
        $threwException = false;
        try {
            $statement = new AddStatement(new Iri(self::SRC_URI), new PrefixedIri('ex', 'dst'));
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
