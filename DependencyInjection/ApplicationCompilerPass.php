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
        if ($container->hasDefinition('varspool_websocket.application_manager')) {
            $definition = $container->getDefinition('varspool_websocket.application_manager');

            foreach ($container->findTaggedServiceIds('varspool_websocket.application') as $id => $attributes) {
                $definition->addMethodCall('addNamedApplication', array(new Reference($id)));
            }
        }

        if ($container->hasDefinition('varspool_websocket.multiplex')) {
            $definition = $container->getDefinition('varspool_websocket.multiplex');

            foreach ($container->findTaggedServiceIds('varspool_websocket.multiplex_subscription') as $id => $attributes) {
                if (!isset($attributes[0]['topic']) || !$attributes[0]['topic']) {
                    throw new \Exception('You must give subscription tags a topic attribute');
                }
                $definition->addMethodCall('addSubscription', array($attributes[0]['topic'], new Reference($id)));
            }
        }
    }
}