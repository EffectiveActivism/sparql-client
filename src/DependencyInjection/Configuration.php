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
                    ->info('Provide an endpoint for a SPARQL server.')
                    ->isRequired()
                ->end() // sparql_endpoint
                ->scalarNode('shacl_endpoint')
                    ->info('Optionally provide an endpoint for a SHACL validation service.')
                    ->defaultValue('')
                ->end() // shacl_endpoint
                ->arrayNode('namespaces')
                    ->info('Namespaces that are used in queries and updates.')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end() // namespaces
            ->end()
        ;
        return $treeBuilder;
    }
}
