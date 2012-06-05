<?php

namespace Varspool\WebsocketBundle\Multiplex;

use Varspool\WebsocketBundle\Application\MultiplexApplication;

use WebSocket\Connection;

use Varspool\WebsocketBundle\Multiplex\Protocol;

class Channel
{
    /**
     * The topic name of the channel
     *
     * @var string
     */
    protected $topic;

    /**
     * Subscribers to this channel
     *
     * @var array<Listener>
     */
    protected $listeners = array();

    /**
     * Connection listeners
     *
     * @var array<ConnectionListener>
     */
    protected $connectionListeners = array();

    /**
     * Clients subscribed to this channel
     *
     * @var Listener
     */
    protected $clients = array();

    /**
     * Constructor
     *
     * @param string $topic
     */
    public function __construct($topic, MultiplexApplication $application)
    {
        $this->topic = $topic;
        $this->application = $application;
    }

    /**
     * Get the multiplexed topic name
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Adds a listener
     *
     * @param Listener $listener
     */
    public function subscribe(Listener $listener)
    {
        $this->listeners[] = $listener;
        if ($listener instanceof ConnectionListener) {
            $this->connectionListeners[] = $listener;
        }
    }

    /**
     * Unsubscribe the given listener
     *
     * @param Listener $listener
     */
    public function unsubscribe(Listener $listener)
    {
        $index = array_search($listener, $this->listeners, true);
        if ($index) {
            unset($this->listeners[$index]);
        }

        $index = array_search($listener, $this->connectionListeners, true);
        if ($index) {
            unset($this->connectionListeners[$index]);
        }
    }

    /**
     * Gets the listeners
     *
     * @return array<\Varspool\WebsocketBundle\Multiplex\Listener>
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * Gets the connection listeners
     *
     * @return array<\Varspool\WebsocketBundle\Multiplex\ConnectionListener>
     */
    public function getConnectionListeners()
    {
        return $this->connectionListeners;
    }

    /**
     * Adds a client to the channel
     *
     * @param Listener $client
     */
    public function subscribeClient(Connection $client)
    {
        $this->clients[$client->getClientId()] = $client;
    }

    /**
     * Unsubscribe the given client
     *
     * @param Connection $client Websocket connection
     */
    public function unsubscribeClient(Connection $client)
    {
        unset($this->clients[$client->getClientId()]);
    }

    public function isClientSubscribed(Connection $client)
    {
        return array_key_exists($client->getClientId(), $this->clients);
    }

    public function getClient($id)
    {
        return $this->clients[$id];
    }

    /**
     * Sends a message through the channel
     *
     * @param string $message
     * @return array
     */
    public function send(
        $message,
        $type = 'text',
        $masked = false,
        array $options = array()
    ) {
        $except = isset($options['except'])
                        ? $options['except']
                        : false;

        $clients = $this->clients;

        if ($except instanceof Connection) {
            $except = array($except);
        }

        if (is_array($except)) {
            foreach ($except as $connection) {
                if ($connection instanceof Connection) {
                    unset($clients[$connection->getClientId()]);
                }
            }
        }

        $collected = array();
        foreach ($clients as $client) {
            $collected[] = $client->send(Protocol::toString(
                Protocol::TYPE_MESSAGE,
                $this->topic,
                $message
            ), $type, $masked);
        }
        return $collected;
    }

    /**
     * Receives a message from the channel
     *
     * Some event source, like a multiplex server application, will call this
     * method to notify subscribers of a message on this channel.
     *
     * @param string $message
     * @return array
     */
    public function receive($message, Connection $client)
    {
        if (!$this->isClientSubscribed($client)) {
            $this->subscribeClient($client);
        }

        $collected = array();
        foreach ($this->listeners as $listener) {
            $collected[] = $listener->onMessage($this, $message, $client);
        }
        return $collected;
    }
}