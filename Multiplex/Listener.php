<?php

namespace Varspool\WebsocketBundle\Multiplex;

use Wrench\Connection;

/**
 * Multiplex listener
 *
 * Represents an object that can be subscribed to a multiplex channel. The
 * multiplex websocket server implementation takes listener objects as an
 * argument to subscribe() and unsubscribe().
 *
 * Note that a single listener may listen on multiple topics.
 */
interface Listener
{
    /**
     * @param Channel $channel   The channel is an object that holds all the active
     *          client connections to a given topic, and all the server-side
     *          subscribers. You can ->send($message) to the channel to broadcast
     *          it to all the subscribed client connections. ->getTopic() identifies
     *          the topic the message was received on.
     *
     * @param string $message    The received message, as a string
     *
     * @param Connection $client The client connection the message was received
     *          from. You can ->send() to the client, but it is a raw Websocket
     *          connection, so if you want to send a multiplexed message to a single
     *          client, you'll probably use
     *          `Varspool\WebsocketBundle\Multiplex\Protocol::toString($type, $topic, $payload)`
     *          and the Protocol::TYPE_MESSAGE constant.
     */
    public function onMessage(Channel $channel, $message, Connection $client);
}