<?php

/**
 * Zend_Mail provides no way to modify context options of a connection stream.
 * This class provides a stream context aware implementation for opening socket
 * connection, shared by Zefram_Mail_Protocol classes.
 *
 * @see https://github.com/zendframework/zf1/issues/709
 *
 * @category Zefram
 * @package  Zefram_Mail
 * @author   xemlock
 * @internal
 */
abstract class Zefram_Mail_Protocol_Trait
{
    const TIMEOUT_CONNECTION = Zend_Mail_Protocol_Abstract::TIMEOUT_CONNECTION;

    /**
     * Extract and validate stream context from 'stream_context' key of given
     * configuration array
     *
     * @param array $config
     * @return array|resource|null
     * @throws Zend_Mail_Protocol_Exception
     */
    public static function extractStreamContext(array $config)
    {
        if (isset($config['stream_context'])) {
            return self::validateStreamContext($config['stream_context']);
        }
        return null;
    }

    /**
     * Check if given value is a stream context resource, or can be passed
     * as options to {@link stream_context_create()}
     *
     * @param mixed $context
     * @return array|resource
     * @throws Zend_Mail_Protocol_Exception
     */
    public static function validateStreamContext($context)
    {
        if (is_resource($context)) {
            if (get_resource_type($context) !== 'stream-context') {
                throw new Zend_Mail_Protocol_Exception('Invalid stream context resource given');
            }
        } elseif (!is_array($context)) {
            throw new Zend_Mail_Protocol_Exception('Expecting either a stream context resource or array, got ' . gettype($context));
        }
        return $context;
    }

    /**
     * Stream-context aware implementation for opening socket connection,
     * shared by Zefram_Mail_Protocol classes
     *
     * @param string $remote
     * @param array|resource $context OPTIONAL
     * @return resource
     * @throws Zend_Mail_Protocol_Exception
     */
    public static function connect($remote, $context = null)
    {
        $context = null === $context ? array() : self::validateStreamContext($context);

        if (is_array($context)) {
            $context = stream_context_create($context);
        }

        $errorNum = 0;
        $errorStr = '';

        $socket = @stream_socket_client($remote, $errorNum, $errorStr, self::TIMEOUT_CONNECTION, STREAM_CLIENT_CONNECT, $context);

        if ($socket === false) {
            if ($errorNum === 0) {
                $errorStr = 'Could not open socket';
            }
            throw new Zend_Mail_Protocol_Exception($errorStr);
        }

        if (($result = stream_set_timeout($socket, self::TIMEOUT_CONNECTION)) === false) {
            throw new Zend_Mail_Protocol_Exception('Could not set stream timeout');
        }

        return $socket;
    }
}
