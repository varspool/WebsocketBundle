<?php

namespace Varspool\WebsocketBundle\Application;

/**
 * It's recommended that all your applications extend this interface.
 *
 * Once you've implemented this interface, you can get your applications
 * served by the websocket:listen console command: just expose them
 * as a service with the varspool_websocket.application tag.
 */
interface NamedApplication
{
    /**
     * Returns a simple string name representing this application. This name
     * is used to serve more than one application from a single websocket server.
     *
     * @return string
     */
    public function getName();
}
