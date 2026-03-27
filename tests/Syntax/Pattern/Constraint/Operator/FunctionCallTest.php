<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\GreaterThan;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\FunctionCall;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FunctionCallTest extends KernelTestCase
{
    public function testFunctionCallWithPrefixedIri()
    {
        $functionCall = new FunctionCall(
            new PrefixedIri('geof', 'distance'),
            new Variable('point1'),
            new Variable('point2'),
            new PrefixedIri('uom', 'metre'),
        );
        $this->assertEquals('geof:distance(?point1, ?point2, uom:metre)', $functionCall->serialize());
    }

    public function testFunctionCallWithFullIri()
    {
        $functionCall = new FunctionCall(
            new Iri('http://example.org/func'),
            new Variable('x'),
        );
        $this->assertEquals('<http://example.org/func>(?x)', $functionCall->serialize());
    }

    public function testFunctionCallWithNoArguments()
    {
        $functionCall = new FunctionCall(new PrefixedIri('ex', 'now'));
        $this->assertEquals('ex:now()', $functionCall->serialize());
    }

    public function testFunctionCallWithNestedOperator()
    {
        $functionCall = new FunctionCall(
            new PrefixedIri('ex', 'check'),
            new GreaterThan(new Variable('x'), new PlainLiteral('5')),
        );
        $this->assertEquals('ex:check(?x > """5""")', $functionCall->serialize());
    }

    public function testGetters()
    {
        $iri = new PrefixedIri('geof', 'distance');
        $arg1 = new Variable('point1');
        $arg2 = new Variable('point2');
        $functionCall = new FunctionCall($iri, $arg1, $arg2);
        $this->assertSame($iri, $functionCall->getFunctionIri());
        $this->assertSame([$arg1, $arg2], $functionCall->getArguments());
        $this->assertSame([$iri, $arg1, $arg2], $functionCall->getExpressions());
    }
}
