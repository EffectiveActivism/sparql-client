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
        $namespace = ['schema' => 'http://schema.org/'];
        $class = new class($namespace) extends AbstractStatement {};
        $this->assertEquals(self::QUERY, $class->toQuery());
    }

    public function testExceptions()
    {
        // Test statement with invalid namespace prefix.
        $namespace = ['!invalid' => 'http://schema.org/'];
        $threwException = false;
        try {
            new class($namespace) extends AbstractStatement {};
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with invalid namespace url.
        $namespace = ['schema' => 'invalid_url'];
        $threwException = false;
        try {
            new class($namespace) extends AbstractStatement {};
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
