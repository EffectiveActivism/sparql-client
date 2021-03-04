<?php

namespace EffectiveActivism\SparQlClient\Tests\Kernel;

use EffectiveActivism\SparQlClient\EffectiveActivismSparQlClientBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class TestingKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new EffectiveActivismSparQlClientBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            // Configure symfony/framework.
            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'session' => [
                    'enabled' => true,
                    'storage_id' => 'session.storage.mock_file',
                ],
                'router' => [
                    'resource' => '~',
                ],
            ]);
            $container->loadFromExtension('sparql_client', [
                'sparql_client' => [
                    'sparql_endpoint' => [
                        'hostname' => 'test-sparql-endpoint',
                        'port' => 9999,
                    ],
                ],
            ]);
        });
    }
}
