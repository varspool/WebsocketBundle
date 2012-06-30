<?php

namespace Varspool\WebsocketBundle\Multiplex;

use Wrench\Connection;

interface RemoteLogger
{
    public function log($message, $level = 'info', Connection $client = null);
}