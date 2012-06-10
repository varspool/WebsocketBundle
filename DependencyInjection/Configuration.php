<?php

namespace Varspool\WebsocketBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('varspool_websocket');

        $this->addServerSection($rootNode);

        return $treeBuilder;
    }

    protected function addServerSection($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('servers')
                    ->addDefaultsIfNotSet()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('class')->defaultValue('Varspool\WebsocketBundle\Server\Server')->end()
                            ->scalarNode('listen')->defaultValue('ws://localhost')->end()
                            ->booleanNode('ssl')->defaultValue(false)->end()
                            ->scalarNode('max_clients')->defaultValue(30)->end()
                            ->scalarNode('max_connections_per_ip')->defaultValue(5)->end()
                            ->scalarNode('max_requests_per_minute')->defaultValue(50)->end()
                            ->booleanNode('check_origin')->defaultValue(true)->end()
                            ->arrayNode('allow_origin')
                                ->defaultValue(array('localhost'))
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('applications')
                                ->requiresAtLeastOneElement()
                                ->isRequired()
                                ->addDefaultsIfNotSet()
                                ->defaultValue(array('echo'))
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}