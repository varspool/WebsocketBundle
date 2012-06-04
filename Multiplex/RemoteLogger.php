<?php

namespace Varspool\WebsocketBundle\Multiplex;

use WebSocket\Connection;

interface RemoteLogger
{
    public function log($message, $level = 'info', Connection $client = null);
}