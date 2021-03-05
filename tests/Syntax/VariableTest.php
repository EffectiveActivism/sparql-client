<?php

namespace Syntax;

use EffectiveActivism\SparQlClient\Syntax\Term\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class VariableTest extends KernelTestCase
{
    const INVALID_VARIABLES = [
        '‿lorem',
        '?lorem',
        'lo rem',
        'lorem;',
        'lorem.',
    ];

    const VALID_VARIABLES = [
        '1lorem',
        'lorem‿',
        '123456',
        '_lorem',
        '_‿‿‿‿‿',
    ];

    public function testInvalidVariable()
    {
        foreach (self::INVALID_VARIABLES as $invalidVariable) {
            $variable = new Variable($invalidVariable);
            $this->assertFalse($variable->validate());
        }
    }

    public function testValidVariable()
    {
        foreach (self::VALID_VARIABLES as $validVariable) {
            $variable = new Variable($validVariable);
            $this->assertTrue($variable->validate());
        }
    }
}
