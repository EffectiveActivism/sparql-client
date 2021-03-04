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
            ->children()
                ->arrayNode('sparql_endpoint')
                    ->children()
                        ->scalarNode('hostname')->end()
                        ->integerNode('port')->end()
                    ->end()
                ->end() // sparql_endpoint
            ->end()
        ;
        return $treeBuilder;
    }
}
