<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Order;

use EffectiveActivism\SparQlClient\Syntax\Order\Desc;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DescTest extends KernelTestCase
{
    const SERIALIZED_DESC = 'DESC( ?foo )';

    public function testDesc()
    {
        $variable = new Variable('foo');
        $asc = new Desc($variable);
        $this->assertEquals(self::SERIALIZED_DESC, $asc->serialize());
    }
}
