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
                ->scalarNode('query_endpoint')
                    ->info('Provide an endpoint for SPARQL query operations (SELECT, ASK, CONSTRUCT, DESCRIBE).')
                    ->isRequired()
                ->end() // query_endpoint
                ->scalarNode('update_endpoint')
                    ->info('Provide an endpoint for SPARQL update operations (INSERT, DELETE, CLEAR, DROP, CREATE, REPLACE).')
                    ->isRequired()
                ->end() // update_endpoint
                ->scalarNode('shacl_endpoint')
                    ->info('Optionally provide an endpoint for a SHACL validation service.')
                    ->defaultValue('')
                ->end() // shacl_endpoint
            ->end()
        ;
        return $treeBuilder;
    }
}
