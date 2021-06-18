<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Order;

use EffectiveActivism\SparQlClient\Syntax\Order\Asc;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AscTest extends KernelTestCase
{
    const SERIALIZED_ASC = 'ASC( ?foo )';

    public function testAsc()
    {
        $variable = new Variable('foo');
        $asc = new Asc($variable);
        $this->assertEquals(self::SERIALIZED_ASC, $asc->serialize());
    }
}
