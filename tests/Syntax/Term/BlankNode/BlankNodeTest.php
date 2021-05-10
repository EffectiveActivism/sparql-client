<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\BlankNode;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\BlankNode\BlankNode;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BlankNodeTest extends KernelTestCase
{
    public function testBlankNode()
    {
        $blankNode = new BlankNode('lorem');
        $this->assertEquals('_:lorem', $blankNode->serialize());
        $this->assertEquals('lorem', $blankNode->getRawValue());
        $threwException = false;
        try {
            new BlankNode('_:lorem');
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
