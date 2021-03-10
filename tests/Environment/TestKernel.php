<?php

namespace EffectiveActivism\SparQlClient\Tests\Environment;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new TestBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'session' => [
                    'enabled' => true,
                    'storage_id' => 'session.storage.mock_file',
                ],
                'router' => [
                    'resource' => '~',
                    'utf8' => true,
                ],
            ]);
            $container->loadFromExtension('sparql_client', [
                'sparql_endpoint' => 'http://test-sparql-endpoint:9999/blazegraph/sparql',
                'namespaces' => [
                    'schema' => 'http://schema.org/',
                ]
            ]);
        });
    }
}
