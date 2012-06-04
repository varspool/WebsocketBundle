<?php

namespace Varspool\WebsocketBundle\Application;

use Varspool\WebsocketBundle\Application\Application;
use Varspool\WebsocketBundle\Application\NamedApplication;

/**
 * Example application for VarspoolWebsocketBundle: echo server
 *
 * As you can see, this application class implements
 * Varspool\WebsocketBundle\Application\NamedApplication. This is recommended,
 * otherwise you'll have to manually add your application to the application
 * manager (you won't be able to use the varspool_websocket.application tag.
 *
 * Most of the echo server is upstream.
 */
class EchoApplication extends Application
{
    protected $clients = array();

    /**
     * @see Varspool\WebsocketBundle\Application.NamedApplication::getName()
     */
    public function getName()
    {
        return 'echo';
    }

    public function onConnect($client)
    {
        $this->clients[] = $client;
    }

    public function onDisconnect($client)
    {
        $key = array_search($client, $this->clients);
        if ($key) {
            unset($this->clients[$key]);
        }
    }

    public function onData($data, $client)
    {
        foreach ($this->clients as $sendto) {
            $sendto->send($data);
        }
    }
}