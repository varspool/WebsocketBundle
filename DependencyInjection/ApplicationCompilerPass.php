<?php

namespace Varspool\WebsocketBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ApplicationCompilerPass implements CompilerPassInterface
{
    /**
     * @see Symfony\Component\DependencyInjection\Compiler.CompilerPassInterface::process()
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('varspool_websocket.server_manager')) {
            $definition = $container->getDefinition('varspool_websocket.server_manager');

            foreach ($container->findTaggedServiceIds('varspool_websocket.application') as $id => $attributes) {
                if (!isset($attributes[0]['key']) || !$attributes[0]['key']) {
                    throw new \Exception('You must give varspool_websocket.application tags a key attribute');
                }
                $definition->addMethodCall('addApplication', array($attributes[0]['key'], new Reference($id)));
            }
        }

        if ($container->hasDefinition('varspool_websocket.multiplex')) {
            $definition = $container->getDefinition('varspool_websocket.multiplex');

            foreach ($container->findTaggedServiceIds('varspool_websocket.multiplex_listener') as $id => $attributes) {
                if (!isset($attributes[0]['topic']) || !$attributes[0]['topic']) {
                    throw new \Exception('You must give varspool_websocket.listener tags a topic attribute');
                }
                $definition->addMethodCall('addListener', array($attributes[0]['topic'], new Reference($id)));
            }
        }
    }
}