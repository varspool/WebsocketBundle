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
     * @var Subscription
     */
    protected $subscriptions = array();

    /**
     * Clients subscribed to this channel
     *
     * @var Subscription
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
     * Adds a subscription
     *
     * @param Subscription $subscription
     */
    public function subscribe(Subscription $subscription)
    {
        $this->subscriptions[] = $subscription;
    }

    /**
     * Unsubscribe the given subscription
     *
     * @param Subscription $subscription
     */
    public function unsubscribe(Subscription $subscription)
    {
        $index = array_search($subscription, $this->subscriptions);
        if ($index) {
            unset($this->subscriptions[$index]);
        }
    }

    /**
     * Adds a client to the channel
     *
     * @param Subscription $client
     */
    public function subscribeClient(Connection $client)
    {
        $this->clients[] = $client;
    }

    /**
     * Unsubscribe the given client
     *
     * @param Connection $client Websocket connection
     */
    public function unsubscribeClient(Connection $client)
    {
        $index = array_search($client, $this->clients);
        if ($index) {
            unset($this->clients[$index]);
        }
    }

    public function isClientSubscribed(Connection $client)
    {

    }WW

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
        $except = isset($options['except']) && $options['except'] instanceof Connection
                        ? $options['except']
                        : false;

        $clients = $this->clients;

        if ($except) {
            $clients = array_filter($clients, function ($client) use ($except) {
                return $client !== $except;
            });
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
        foreach ($this->subscriptions as $subscription) {
            $collected[] = $subscription->onMessage($this, $message, $client);
        }
        return $collected;
    }
}