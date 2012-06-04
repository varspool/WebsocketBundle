<?php

namespace Varspool\WebsocketBundle\Multiplex;

/**
 * Protocol details
 */
class Protocol
{
    /**#@+
     * Protocol strings
     *
     * @var string
     */
    const TYPE_MESSAGE     = 'msg';
    const TYPE_SUBSCRIBE   = 'sub';
    const TYPE_UNSUBSCRIBE = 'uns';
    /**#@-*/

    /**
     * Converts a command to a string
     *
     * @param string $type
     * @param string $topic
     * @param string $payload
     * @return string
     */
    public static function toString($type, $topic, $payload = null)
    {
        $args = array($type, $topic);
        if ($payload) {
            $args[] = $payload;
        }
        return implode($args, ',');
    }

    /**
     * Converts a string into a command
     *
     * @param string $string
     * @return array(string $type, string $topic, string $payload)
     */
    public static function fromString($string)
    {
        $tokens = explode(',', $string);

        if (!$tokens || count($tokens) < 2) {
            throw new ProtocolException('Invalid command tokens');
        }

        return array(
            $tokens[0],
            $tokens[1],
            $payload = isset($tokens[2]) ? implode(',', array_slice($tokens, 2)) : false
        );
    }
}