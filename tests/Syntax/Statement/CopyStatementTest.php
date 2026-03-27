<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Statement\CopyStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CopyStatementTest extends KernelTestCase
{
    const SRC_URI = 'http://example.org/src';
    const DST_URI = 'http://example.org/dst';

    const COPY_STATEMENT = 'COPY GRAPH <http://example.org/src> TO GRAPH <http://example.org/dst>';
    const COPY_SILENT_STATEMENT = 'COPY SILENT GRAPH <http://example.org/src> TO GRAPH <http://example.org/dst>';

    public function testCopyStatement()
    {
        $src = new Iri(self::SRC_URI);
        $dst = new Iri(self::DST_URI);
        $statement = new CopyStatement($src, $dst);
        $this->assertEquals(self::COPY_STATEMENT, $statement->toQuery());
        $this->assertEquals($src, $statement->getSourceGraph());
        $this->assertEquals($dst, $statement->getDestinationGraph());
    }

    public function testCopySilentStatement()
    {
        $statement = (new CopyStatement(new Iri(self::SRC_URI), new Iri(self::DST_URI)))->silent();
        $this->assertEquals(self::COPY_SILENT_STATEMENT, $statement->toQuery());
    }

    public function testCopyStatementUndefinedSourcePrefix()
    {
        $threwException = false;
        try {
            $statement = new CopyStatement(new PrefixedIri('ex', 'src'), new Iri(self::DST_URI));
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }

    public function testCopyStatementUndefinedDestinationPrefix()
    {
        $threwException = false;
        try {
            $statement = new CopyStatement(new Iri(self::SRC_URI), new PrefixedIri('ex', 'dst'));
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
