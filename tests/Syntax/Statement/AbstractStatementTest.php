<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Statement\AbstractStatement;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractStatementTest extends KernelTestCase
{
    const QUERY = 'PREFIX schema: <http://schema.org/> ';

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
}
