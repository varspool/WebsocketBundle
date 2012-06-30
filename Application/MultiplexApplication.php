<?php

namespace Varspool\WebsocketBundle\Application;

use Varspool\WebsocketBundle\Multiplex\RemoteLogger;

use Wrench\Connection;

use Varspool\WebsocketBundle\Application\Application;
use Varspool\WebsocketBundle\Multiplex\Channel;
use Varspool\WebsocketBundle\Multiplex\Protocol;
use Varspool\WebsocketBundle\Multiplex\Listener;

use \Exception;
use \InvalidArgumentException;
use \stdClass;

/**
 * Multiplex application
 *
 * This provides the server side of an implementation of the SockJS multiplex
 * protocol.
 *
 * To send messages to clients on a topic, grab a channel and send:
 *   ->getChannel($topic)->send($message)
 *
 * To receive messages from clients on a topic, grab a channel and subscribe to
 * it:
 *   ->getChannel($topic)->subscribe($listener)
 *   ->getChannel($topic)->unsubscribe($listener)
 *
 * Your listener object should implement the Listener interface.
 *
 * Logging channels are used to facilitate bi-directional logging. The server
 * subscribes to TOPIC_LOG_CLIENT. Clients should send messages on this
 * topic for them to be logged using the server's logging mechanism. Clients
 * can optionally subscribe to TOPIC_LOG_SERVER to receieve server logging
 * information. Both facilities are only available when debug is true.
 *
 * Server logging will often only be sent to the client that caused the error.
 */
class MultiplexApplication extends Application implements Listener, RemoteLogger
{
    /**#@+
     * Logging topics
     *
     * @var string
     */
    const TOPIC_LOG_SERVER = 'log_server';
    const TOPIC_LOG_CLIENT = 'log_client';
    /**#@-*/

    /**
     * Debug mode
     *
     * Logs extra information
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     * Allow remote logging
     *
     * Whether events to and from the server and client log channels will
     * be logged. Also used to disable this logging on disconnect.
     *
     * @var boolean
     */
    protected $remoteLogging = true;

    /**
     * Active channels
     *
     * @var array<string => Channel>
     */
    protected $channels = array();

    /**
     * Constructor
     *
     * @param boolean $debug
     */
    public function __construct($debug = false)
    {
        $this->debug = $debug;
        parent::__construct();
    }

    /**
     * @see Varspool\WebsocketBundle\Application.Application::getName()
     */
    public function getName()
    {
        return 'multiplex';
    }

    /**
     * Gets the named channel
     *
     * @param string $topic
     * @return Channel
     */
    public function getChannel($topic)
    {
        if (!isset($this->channels[$topic])) {
            $this->channels[$topic] = new Channel($topic, $this);
        }
        return $this->channels[$topic];
    }

    /**
     * Adds the given listener
     *
     * @param string $topic
     * @param Listener $listener
     */
    public function addListener($topic, Listener $listener)
    {
        $this->log('Adding listener to ' . $topic, 'info');
        return $this->getChannel($topic)->subscribe($listener);
    }

    /**
     * @see WebSocket\Application.Application::onConnect()
     */
    public function onConnect($client)
    {
        parent::onConnect($client);

        if ($this->debug) {
            $this->log('Subscribing to log channels', 'info');
            $this->getChannel(self::TOPIC_LOG_CLIENT)->subscribe($this);
            $this->getChannel(self::TOPIC_LOG_SERVER)->subscribeClient($client);
        }

        foreach ($this->channels as $channel) {
            foreach ($channel->getListeners() as $listener) {
                if ($listener instanceof ConnectionListener) {
                    $listener->onConnect($client, $channel);
                }
            }
        }
    }

    /**
     * @see WebSocket\Application.Application::onDisconnect()
     */
    public function onDisconnect($client)
    {
        if ($this->debug) {
            // Disallow remote logging: shutting down
            $this->remoteLogging = false;
            $this->log('Unsubscribing from log channels', 'info');
            $this->getChannel(self::TOPIC_LOG_CLIENT)->unsubscribe($this);
            $this->getChannel(self::TOPIC_LOG_SERVER)->unsubscribeClient($client);
        }

        foreach ($this->channels as $channel) {
            foreach ($channel->getListeners() as $listener) {
                if ($listener instanceof ConnectionListener) {
                    $listener->onDisconnect($client, $channel);
                }
            }
            $channel->unsubscribeClient($client);
        }

        parent::onDisconnect($client);
    }

    /**
     * Log message receiver
     *
     * @see Varspool\WebsocketBundle\Multiplex.Listener::onMessage()
     */
    public function onMessage(Channel $channel, $message, Connection $client = null)
    {
        if ($channel->getTopic() == self::TOPIC_LOG_CLIENT && $this->debug) {
            $this->log('Received client log: ' . $message, 'notice');
        }
    }

    /**
     * @see WebSocket\Application.Application::onData()
     */
    public function onData($data, $client)
    {
        try {
            $command = Protocol::fromString($data);
        } catch (Exception $e) {
            $this->log($e, 'err', $client);
            return;
        }

        if (!$command) {
            $this->log('No command', 'err', $client);
            return;
        }

        list($type, $topic, $payload) = $command;

        switch ($type) {
            case Protocol::TYPE_SUBSCRIBE:
                $this->log('Client asked to be subscribed to ' . $topic, 'info');
                $this->getChannel($topic)->subscribeClient($client);
                return;
            case Protocol::TYPE_MESSAGE:
                if (!$payload) {
                    $this->log('No payload', $client);
                    return;
                }
                $this->log('Received message on ' . $topic . ': ' . $payload, 'info');
                $this->getChannel($topic)->receive($payload, $client);
                return;
            case Protocol::TYPE_UNSUBSCRIBE:
                $this->log('Client asked to be unsubscribed from ' . $topic, 'info');
                $this->getChannel($topic)->unsubscribeClient($client);
                return;
            default:
                $this->log('Unknown type: ' . $type, 'err', $client);
                break;
        }
    }

    /**
     * Logs an application error
     *
     * @see Varspool\WebsocketBundle\Application.Application::log()
     */
    public function log($message, $level = 'info', Connection $client = null)
    {
        if ($this->debug) {
            parent::log($message, $level);

            if ($this->remoteLogging) {
                $log = new stdClass;
                $log->type = 'log';
                $log->level = $level;
                $log->message = $message;

                $log = $this->getJsonPayload($log);

                if ($client) {
                    $client->send(Protocol::toString(
                        Protocol::TYPE_MESSAGE, self::TOPIC_LOG_SERVER, $log
                    ));
                } else {
                    $this->getChannel(self::TOPIC_LOG_SERVER)->send($log);
                }
            }
        }
    }

    /**
     * Gets a JSON payload
     *
     * @param \stdClass $payload
     * @return string
     */
    public function getJsonPayload(\stdClass $payload)
    {
        return json_encode($payload, false);
    }
}
