<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Aggregate;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\Sample;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SampleTest extends KernelTestCase
{
    public function testSample()
    {
        $variable = new Variable('amount');
        $operator = new Sample($variable);
        $this->assertEquals('SAMPLE(?amount)', $operator->serialize());
    }

    public function testSampleDistinct()
    {
        $variable = new Variable('amount');
        $operator = new Sample($variable);
        $operator->distinct();
        $this->assertEquals('SAMPLE(DISTINCT ?amount)', $operator->serialize());
    }
}
