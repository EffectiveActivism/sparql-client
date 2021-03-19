<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax;

use EffectiveActivism\SparQlClient\Syntax\Term\AbstractTerm;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractTermTest extends KernelTestCase
{
    const VARIABLE_NAME = 'subject';

    public function testAbstractTerm()
    {
        $abstractTermClass = new class() extends AbstractTerm {
            public function serialize(): string
            {
                return '';
            }
            public function getRawValue(): string
            {
                return '';
            }
        };
        $abstractTermClass->setVariableName(self::VARIABLE_NAME);
        $this->assertEquals(self::VARIABLE_NAME, $abstractTermClass->getVariableName());
    }
}
