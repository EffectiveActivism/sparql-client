<?php declare(strict_types = 1);

namespace EffectiveActivism\SparQlClient\DependencyInjection;

use EffectiveActivism\SparQlClient\Client\ShaclClientInterface;
use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SparQlClientExtension extends Extension
{
    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $definition = $container->getDefinition(SparQlClientInterface::class);
        $definition->addArgument($config);
        $definition = $container->getDefinition(ShaclClientInterface::class);
        $definition->addArgument($config);
    }

    public function getAlias(): string
    {
        return 'sparql_client';
    }
}
