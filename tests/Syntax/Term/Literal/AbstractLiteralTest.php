<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Literal;

use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractLiteralTest extends KernelTestCase
{
    public function testExceptions()
    {
        $this->expectException(InvalidArgumentException::class);
        new class(" \n ") extends AbstractLiteral {

            public function serialize(): string
            {
                return '';
            }

            public function getType(): string
            {
                return '';
            }
        };
    }
}
