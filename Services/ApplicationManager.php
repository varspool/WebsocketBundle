<?php

namespace Varspool\WebsocketBundle\Services;

use WebSocket\Server\Application\Application;
use Varspool\WebsocketBundle\Application\NamedApplication;

use \IteratorAggregate;
use \ArrayIterator;

/**
 * Application manager
 *
 * Gathers a set of applications and names, usually so that they can be served
 * under those names. Names should be simple: lower_case_underscored
 */
class ApplicationManager implements IteratorAggregate
{
    /**
     * @param array<Application>
     */
    protected $applications = array();

    /**
     * @see ArrayObject::getIterator()
     */
    public function getIterator()
    {
        return new ArrayIterator($this->applications);
    }

    /**
     * @param NamedApplication $application
     */
    public function addNamedApplication(NamedApplication $application)
    {
        $this->applications[$application->getName()] = $application;
    }

    /**
     * @param string      $name        The name that will be used when served
     * @param Application $application
     */
    public function addApplication($name, Application $application)
    {
        $this->applications[$name] = $application;
    }
}
