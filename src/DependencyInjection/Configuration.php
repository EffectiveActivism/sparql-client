<?php declare(strict_types = 1);

namespace EffectiveActivism\SparQlClient\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sparql_client');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('sparql_endpoint')
                    ->info('Provide an endpoint for a SPARQL server.')
                    ->isRequired()
                ->end() // sparql_endpoint
                ->scalarNode('shacl_endpoint')
                    ->info('Optionally provide an endpoint for a SHACL validation service.')
                    ->defaultValue('')
                ->end() // shacl_endpoint
            ->end()
        ;
        return $treeBuilder;
    }
}
