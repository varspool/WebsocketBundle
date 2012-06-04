<?php

namespace Varspool\WebsocketBundle\Application;

use WebSocket\Application\Application as BaseApplication;

use \Closure;

/**
 * Overriden because the base class is a singleton and we want to inject our
 * applications using the service container.
 */
abstract class Application extends BaseApplication implements NamedApplication
{
    /**
     * Active clients
     *
     * @var array
     */
    protected $clients = array();

    /**
     * Closure logger
     *
     * @var Closure
     */
    protected $logger;

    /**
     * @see Varspool\WebsocketBundle\Application.NamedApplication::getName()
     */
    abstract public function getName();

    /**
     * Screw singletons, we use DI
     *
     * Overriden, protected in parent
     */
    public function __construct()
    {
        $this->logger = function($message, $level = 'info') {
            echo $level . ': ' . $message . "\n";
        };
    }

    /**
     * Sets the logger
     *
     * @param Closure $logger
     */
    public function setLogger(Closure $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs a message to the server log
     *
     * @param string $message
     * @param string $level
     */
    public function log($message, $level = 'info')
    {
        $message = $this->getName() . ': ' . $message;

        $log = $this->logger;
        $log($message, $level);
    }

    /**
     * @see WebSocket\Application.Application::onConnect()
     */
    public function onConnect($client)
    {
        $this->clients[] = $client;
    }

    /**
     * @see WebSocket\Application.Application::onDisconnect()
     */
    public function onDisconnect($client)
    {
        $key = array_search($client, $this->clients);
        if ($key) {
            unset($this->clients[$key]);
        }
    }

    /**
     * Sends the data to all connected clients
     *
     * @param mixed $data
     * @return array
     */
    public function sendToAll($data)
    {
        $collected = array();
        foreach ($this->clients as $client) {
            $collected[] = $client->send($data);
        }
        return $collected;
    }
}
