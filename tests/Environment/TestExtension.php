<?php declare(strict_types = 1);

namespace EffectiveActivism\SparQlClient\Tests\Environment;

use EffectiveActivism\SparQlClient\Client\SparQlClient;
use EffectiveActivism\SparQlClient\DependencyInjection\Configuration;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class TestExtension extends Extension
{
    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
        $loader->load('services.yml');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $definition = $container->getDefinition(SparQlClient::class);
        $definition->addArgument($config);
    }

    public function getAlias()
    {
        return 'sparql_client';
    }
}
