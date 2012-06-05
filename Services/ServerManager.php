<?php

namespace Varspool\WebsocketBundle\Services;

use \InvalidArgumentException;

/**
 * Server manager class
 *
 * Mainly exists to defer choosing a logger until just before the server is
 * instantiated. This allows the logger to be replaced by an OutputInterface, yet
 * still support Monolog. In fact, because the logger is swapped out with a
 * Closure here, you can pass in your own logging callback.
 */
class ServerManager
{
    /**
     * @var Closure
     */
    protected $logger;

    /**
     * @var array<string => Server>
     */
    protected $servers = array();

    /**
     * @var array<Application>
     */
    protected $applications = array();

    /**
     * @var array<string => array>
     */
    protected $configuration;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = function ($message, $level = 'info') {
            echo $level . ': ' . $message . "\n";
        };
    }

    public function addApplication($key, $application)
    {
        $this->applications[$key] = $application;
    }

    /**
     * Sets the server configuration
     *
     * Called by DI
     *
     * @param array $configuration
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Gets a server by name
     *
     * @param string $name
     * @return
     */
    public function getServer($name)
    {
        if (!isset($this->servers[$name])) {
            return $this->createServer($name);
        }
        return $this->servers[$name];
    }

    /**
     * Creates a server
     *
     * @param string $name
     * @throws InvalidArgumentException
     * @return Server
     */
    public function createServer($name)
    {
        if (!isset($this->configuration[$name])) {
            throw new InvalidArgumentException('Invalid server name');
        }

        $config = $this->configuration[$name];

        $server = new $config['class'](
            $config['host'],
            $config['port'],
            $config['ssl'],
            $this->logger
        );

        $server->setMaxClients($config['max_clients']);
        $server->setMaxConnectionsPerIp($config['max_connections_per_ip']);
        $server->setMaxRequestsPerMinute($config['max_requests_per_minute']);
        $server->setCheckOrigin($config['check_origin']);

        foreach ($config['allow_origin'] as $origin) {
            $server->setAllowedOrigin($origin);
        }

        foreach ($config['applications'] as $key) {
            if (!isset($this->applications[$key])) {
                throw new \RuntimeException('Invalid server config: application ' . $key . ' not found');
            }
            $server->registerApplication($key, $this->applications[$key]);
        }

        return (($this->servers[$name] = $server));
    }

    /**
     * @param \Closure|Monolog\Logger $logger
     * @return void
     */
    public function setLogger($logger)
    {
        if ($logger instanceof Monolog\Logger) {
            $this->logger = function ($message, $level) use ($logger) {
                switch ($level) {
                    case 'info':
                        $logger->info($message);
                        return;
                    case 'warn':
                    default:
                        $logger->warn($message);
                        return;
                }
            };
        } else {
            $this->logger = $logger;
        }
    }
}