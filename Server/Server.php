<?php

namespace Varspool\WebsocketBundle\Server;

use Varspool\WebsocketBundle\Application\Application;
use Wrench\BasicServer as BaseServer;
use \Closure;

/**
 * Overriden to use Symfony logging
 */
class Server extends BaseServer
{
    /**
     * @see Wrench.Server::registerApplication()
     */
    public function registerApplication($key, $application)
    {
        $this->log(sprintf(
            'Registering application: %s (%s)',
            $key,
            get_class($application)
        ), 'info');

        if ($application instanceof Application) {
            $application->setLogger($this->logger);
        } else {
            $this->log(
                'Application uses its own logging!',
                'warning'
            );
        }

        return parent::registerApplication($key, $application);
    }

    /**
     * @see Wrench.Server::log()
     */
    public function log($message, $level = 'info')
    {
        $l = $this->logger;
        $l($message, $level);
    }
}