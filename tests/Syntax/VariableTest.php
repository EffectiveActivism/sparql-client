<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax;

use EffectiveActivism\SparQlClient\Syntax\Term\Variable;
use InvalidArgumentException;
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
            $this->expectException(InvalidArgumentException::class);
            $variable = new Variable($invalidVariable);
        }
    }

    public function testValidVariable()
    {
        foreach (self::VALID_VARIABLES as $validVariable) {
            $this->assertInstanceOf(Variable::class, new Variable($validVariable));
        }
    }
}
