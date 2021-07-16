<?php

/**
 * @category Zefram
 * @package  Zefram_Mail
 * @author   xemlock
 */
class Zefram_Mail_Protocol_Smtp extends Zend_Mail_Protocol_Smtp
{
    /**
     * @var array|resource
     */
    protected $_context;

    /**
     * @var int
     */
    protected $_cryptoMethod;

    public function __construct($host = '127.0.0.1', $port = null, array $config = array())
    {
        parent::__construct($host, $port, $config);
        $this->_context = Zefram_Mail_Protocol_Trait::extractStreamContext($config);

        $this->_cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $this->_cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            $this->_cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        }
    }

    protected function _connect($remote)
    {
        $this->_socket = Zefram_Mail_Protocol_Trait::connect($remote, $this->_context);
        return true;
    }

    /**
     * Implementation with
     * @param string $host
     * @throws Zend_Mail_Protocol_Exception
     */
    public function helo($host = '127.0.0.1')
    {
        // Respect RFC 2821 and disallow HELO attempts if session is already initiated.
        if ($this->_sess === true) {
            throw new Zend_Mail_Protocol_Exception('Cannot issue HELO to existing session');
        }

        // Validate client hostname
        if (!$this->_validHost->isValid($host)) {
            throw new Zend_Mail_Protocol_Exception(join(', ', $this->_validHost->getMessages()));
        }

        // Initiate helo sequence
        $this->_expect(220, 300); // Timeout set for 5 minutes as per RFC 2821 4.5.3.2
        $this->_ehlo($host);

        // If a TLS session is required, commence negotiation
        if ($this->_secure == 'tls') {
            $this->_send('STARTTLS');
            $this->_expect(220, 180);
            if (!stream_socket_enable_crypto($this->_socket, true, $this->_cryptoMethod)) {
                throw new Zend_Mail_Protocol_Exception('Unable to connect via TLS');
            }
            $this->_ehlo($host);
        }

        $this->_startSession();
        $this->auth();
    }
}
