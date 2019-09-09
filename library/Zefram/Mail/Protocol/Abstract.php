<?php

/**
 * Zend_Mail provides no way to modify stream context options of a connection.
 *
 * @see https://github.com/zendframework/zf1/issues/709
 *
 * @category Zefram
 * @package  Zefram_Mail
 * @author   xemlock
 */
abstract class Zefram_Mail_Protocol_Abstract extends Zend_Mail_Protocol_Abstract
{
    /**
     * A stream-context aware implementation for opening socket connection,
     * shared by Zefram_Mail_Protocol classes
     *
     * @param string $remote
     * @param array|resource $context OPTIONAL
     * @return resource
     * @throws Zend_Mail_Protocol_Exception
     * @internal
     */
    public static function _connectStatic($remote, $context = null)
    {
        if (is_resource($context)) {
            if (get_resource_type($context) !== 'stream-context') {
                throw new Zend_Mail_Protocol_Exception('Invalid stream context resource given');
            }
        } elseif (is_array($context) || $context === null) {
            $context = stream_context_create($context);

        } else {
            throw new Zend_Mail_Protocol_Exception('Expecting either a stream context resource or array, got ' . gettype($context));
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
