<?php

namespace EffectiveActivism\SparQlClient\Tests\Environment;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    public function registerBundles(): iterable
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
                'http_method_override' => false,
                'handle_all_throwables' => true,
                'php_errors' => [
                    'log' => true,
                ],
                'annotations' => [
                    'enabled' => false,
                ],
            ]);
            $container->loadFromExtension('sparql_client', [
                'sparql_endpoint' => 'http://test-sparql-endpoint:9999/blazegraph/sparql',
                'shacl_endpoint' => 'http://test-shacl-endpoint',
                'namespaces' => [
                    'schema' => 'http://schema.org/',
                ]
            ]);
        });
    }
}
