<?php

/**
 * Zend_Mail_Transport_Smtp constructor signature differs from the other
 * transports. This class aims to support any of these versions.
 *
 * @uses Zend_Mail
 */
class Zefram_Mail_Transport_Smtp extends Zend_Mail_Transport_Smtp
{
    const LOCALHOST = '127.0.0.1';

    /**
     * Constructor.
     *
     * @param  string|array $host OPTIONAL
     * @param  integer $port OPTIONAL
     * @param  array $config OPTIONAL
     * @return void Zend_Mail_Protocol_Exception
     */
    public function __construct($host = self::LOCALHOST, array $config = null)
    {
        if (is_array($host)) {
            $config = $host;
            $host = isset($config['host']) ? $config['host'] : self::LOCALHOST;
        }
        parent::__construct($host, (array) $config);
    }

    /**
     * Retrieve the connection protocol instance
     *
     * @return Zend_Mail_Protocol_Abstract|null
     */
    public function getConnection()
    {
        return $this->_connection;
    }
}
