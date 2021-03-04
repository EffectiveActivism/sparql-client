<?php

namespace EffectiveActivism\SparQlClient\Tests;

use EffectiveActivism\SparQlClient\Client\SparQlClient;
use EffectiveActivism\SparQlClient\Tests\Kernel\TestingKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ServicesTest extends KernelTestCase
{
    /**
     * @covers \EffectiveActivism\SparQlClient\DependencyInjection\SparQlClientExtension
     */
    public function testServiceWiring()
    {
        $kernel = new TestingKernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();
        $this->assertInstanceOf(SparQlClient::class, $container->get('EffectiveActivism\SparQlClient\Client\SparQlClient'));
    }
}
