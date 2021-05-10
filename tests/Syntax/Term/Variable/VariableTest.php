<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Variable;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
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
            try {
                new Variable($invalidVariable);
                $this->assertFalse(true);
            } catch (SparQlException $exception) {
                $this->assertInstanceOf(SparQlException::class, $exception);
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

    public function testVariableSerialized()
    {
        $variable = new Variable('subject');
        $this->assertEquals('?subject', $variable->serialize());
    }
}
