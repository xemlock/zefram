<?php

/**
 * @category Zefram
 * @package  Zefram_Mail
 * @author   xemlock
 */
class Zefram_Mail_Protocol_Smtp_Auth_Login extends Zend_Mail_Protocol_Smtp_Auth_Login
{
    /**
     * @var array|resource
     */
    protected $_context;

    public function __construct($host = '127.0.0.1', $port = null, array $config = array())
    {
        parent::__construct($host, $port, $config);
        $this->_context = Zefram_Mail_Protocol_Trait::extractStreamContext($config);
    }

    protected function _connect($remote)
    {
        $this->_socket = Zefram_Mail_Protocol_Trait::connect($remote, $this->_context);
        return true;
    }
}
