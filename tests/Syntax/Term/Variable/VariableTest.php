<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Variable;

use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;

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
            try {
                new Variable($invalidVariable);
                $this->assertFalse(true);
            } catch (Throwable $exception) {
                $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            }
        }
    }

    public function testValidVariable()
    {
        foreach (self::VALID_VARIABLES as $validVariable) {
            $this->assertInstanceOf(Variable::class, new Variable($validVariable));
        }
    }

    public function testVariableRawValue()
    {
        $variable = new Variable('subject');
        $this->assertEquals('subject', $variable->getRawValue());
    }
}
