<?php

namespace Varspool\WebsocketBundle\Multiplex;

use WebSocket\Connection;

/**
 * Multiplex subscription
 *
 * Represents an object that can be subscribed to a multiplex channel. The
 * multiplex websocket server implementation takes subscription objects as an
 * argument to subscribe() and unsubscribe().
 *
 * Note that a single subscription may have multiple topics.
 */
interface Subscription
{
    /**
     * When messages are received on any of the topics this subscription has
     * been subscribed to, this method will be called with the message data.
     *
     * @param Channel $channel
     * @param string $message
     */
    public function onMessage(Channel $channel, $message, Connection $client = null);
}