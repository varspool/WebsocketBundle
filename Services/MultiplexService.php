<?php

namespace Varspool\WebsocketBundle\Services;

use Wrench\Connection;

use Varspool\WebsocketBundle\Multiplex\Listener;
use Varspool\WebsocketBundle\Multiplex\ConnectionListener;
use Varspool\WebsocketBundle\Multiplex\RemoteLogger;
use Varspool\WebsocketBundle\Application\MultiplexApplication;

abstract class MultiplexService implements Listener, ConnectionListener, RemoteLogger
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