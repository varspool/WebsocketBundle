<?php

namespace Varspool\WebsocketBundle\Services;

use WebSocket\Connection;

use Varspool\WebsocketBundle\Multiplex\Subscription;
use Varspool\WebsocketBundle\Multiplex\RemoteLogger;
use Varspool\WebsocketBundle\Application\MultiplexApplication;

abstract class MultiplexService implements Subscription, RemoteLogger
{
    /**
     * Multiplex websocket
     *
     * @var MultiplexApplication
     */
    protected $multiplex;

    /**
     * Constructor
     *
     * @param MultiplexApplication $multiplex
     */
    public function __construct(MultiplexApplication $multiplex)
    {
        $this->multiplex = $multiplex;
    }

    /**
     * @see Varspool\WebsocketBundle\Multiplex.RemoteLogger::log()
     */
    public function log($message, $level = 'info', Connection $client = null)
    {
        $this->multiplex->log($message, $level, $client);
    }
}