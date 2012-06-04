<?php

namespace Varspool\WebsocketBundle\DependencyInjection;

use Varspool\WebsocketBundle\VarspoolWebsocketBundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * DI extension
 */
class VarspoolWebsocketExtension extends Extension
{
    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->addServers($config, $container);
    }

    /**
     * Adds server configurations
     *
     * @param array $config
     * @param ContainerBuilder $builder
     */
    protected function addServers(array $config, ContainerBuilder $container)
    {
        $manager = $container->getDefinition('varspool_websocket.server_manager');
        $manager->addMethodCall('setConfiguration', array($config['servers']));
    }

    /**
     * @see Symfony\Component\HttpKernel\DependencyInjection.Extension::getXsdValidationBasePath()
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/';
    }

    /**
     * @see Symfony\Component\HttpKernel\DependencyInjection.Extension::getNamespace()
     */
    public function getNamespace()
    {
        return 'http://www.varspool.com/schema/symfony/1.0/websocket';
    }
}
