<?php declare(strict_types = 1);

namespace EffectiveActivism\SparQlClient\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sparql_client');
        $treeBuilder->getRootNode()
            ->fixXmlConfig('namespace')
            ->children()
                ->scalarNode('sparql_endpoint')
                    ->isRequired()
                ->end() // sparql_endpoint
                ->arrayNode('namespaces')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end() // namespaces
            ->end()
        ;
        return $treeBuilder;
    }
}
