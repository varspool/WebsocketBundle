<?php

namespace Varspool\WebsocketBundle;

use Varspool\WebsocketBundle\DependencyInjection\ApplicationCompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Websocket bundle
 *
 * @author Dominic Scheirlinck <dominic@varspool.com>
 */
class VarspoolWebsocketBundle extends Bundle
{
    /**
     * Format for server service IDs
     *
     * @var string
     */
    const SERVICE_FORMAT = 'varspool_websocket.server_%s';

    /**
     * @see Symfony\Component\HttpKernel\Bundle.Bundle::build()
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ApplicationCompilerPass());
    }

    /**
     * Gets the default service id for a given service name
     *
     * @param string $name
     */
    public static function getServerServiceId($name)
    {
        return sprintf(self::SERVICE_FORMAT, $name);
    }
}
