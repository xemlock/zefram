<?php

/**
 * Zend_Mail_Transport_Smtp constructor signature differs from the other
 * transports. This class aims to support any of these versions.
 *
 * @category Zefram
 * @package  Zefram_Mail
 * @author   xemlock
 * @uses     Zend_Mail
 */
class Zefram_Mail_Transport_Smtp extends Zend_Mail_Transport_Smtp
{
    const LOCALHOST = '127.0.0.1';

    /**
     * @var Zend_Loader
     */
    protected $_pluginLoader;

    /**
     * Constructor.
     *
     * @param  string|array $host OPTIONAL
     * @param  int $port OPTIONAL
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

    /**
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader()
    {
        if (null === $this->_pluginLoader) {
            $this->_pluginLoader = new Zend_Loader_PluginLoader(array(
                'Zend_Mail_'   => 'Zend/Mail/',
                'Zefram_Mail_' => 'Zefram/Mail/',
            ));
        }
        return $this->_pluginLoader;
    }

    /**
     * @param Zend_Loader_PluginLoader_Interface $loader
     * @return $this
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader)
    {
        $this->_pluginLoader = $loader;
        return $this;
    }

    public function _sendMail()
    {
        if (!($this->_connection instanceof Zend_Mail_Protocol_Smtp)) {
            // Check if authentication is required and determine required class
            $connectionClass = 'Protocol_Smtp';
            if ($this->_auth) {
                $connectionClass .= '_Auth_' . ucfirst($this->_auth);
            }
            $connectionClass = $this->getPluginLoader()->load($connectionClass);
            $this->setConnection(new $connectionClass($this->_host, $this->_port, $this->_config));
            $this->_connection->connect();
            $this->_connection->helo($this->_name);
        }
        return parent::_sendMail();
    }
}
