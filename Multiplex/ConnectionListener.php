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
interface ConnectionListener
{
    public function onConnect(Channel $channel, Connection $client);
    public function onDisconnect(Channel $channel, Connection $client);
}