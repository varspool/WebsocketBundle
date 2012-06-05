<?php

namespace Varspool\WebsocketBundle\Server;

use Varspool\WebsocketBundle\Application\Application;
use WebSocket\Server as BaseServer;
use \Closure;

/**
 * Overriden to use Symfony logging
 */
class Server extends BaseServer
{
    /**
     * Logger instance
     *
     * @var Logging closure
     */
    public $logger;

    /**
     * Constructor
     *
     * @param string $host
     * @param int $port
     * @param boolean $ssl
     * @param \Closure|Monolog\Logger
     */
    public function __construct(
        $host = 'localhost',
        $port = 8000,
        $ssl = false,
        Closure $logger
    ) {
        $this->setLogger($logger);

        $this->log(sprintf(
            'Listening on %s:%d with ssl %s',
            $host,
            $port,
            $ssl ? 'on' : 'off'
        ), 'info');

        parent::__construct($host, $port, $ssl);
    }

    /**
     * Sets the logger to use
     *
     * @param Closure $logger
     */
    public function setLogger(Closure $logger)
    {
        $this->logger = $logger;
        foreach ($this->applications as $application) {
            if ($application instanceof Application) {
                $this->application->setLogger($this->logger);
            }
        }
    }

    /**
     * @see WebSocket.Server::registerApplication()
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
     * @see WebSocket.Server::log()
     */
    public function log($message, $level = 'info')
    {
        $l = $this->logger;
        $l($message, $level);
    }
}