<?php

/**
 * @package   Zefram_Validate
 * @uses      Zend_Validate
 * @uses      Zefram_Url
 * @author    xemlock
 * @version   2015-12-27
 */
class Zefram_Validate_Uri extends Zend_Validate_Abstract
{
    const INVALID              = 'uriInvalid';
    const NOT_URI              = 'notUri';
    const SCHEME_NOT_ALLOWED   = 'uriSchemeNotAllowed';
    const HOSTNAME_NOT_ALLOWED = 'uriHostnameNotAllowed';

    protected $_messageTemplates = array(
        self::INVALID              => "Invalid type given. String expected",
        self::NOT_URI              => "The input does not appear to be a valid URI",
        self::SCHEME_NOT_ALLOWED   => "URI scheme '%scheme%' is not allowed",
        self::HOSTNAME_NOT_ALLOWED => "The input does not appear to have a valid URI hostname",
    );

    protected $_messageVariables = array(
        'scheme' => '_scheme',
    );

    /**
     * List of allowed schemes
     * @var array
     */
    protected $_allowedSchemes = array('http', 'https');

    /**
     * Hostname validator instance
     * @var Zend_Validate_Hostname
     */
    protected $_hostnameValidator;

    /**
     * Scheme of currently validated URI
     * @var string
     */
    protected $_scheme;

    /**
     * @param array|object $options
     */
    public function __construct($options = null)
    {
        if (is_object($options) && method_exists($options, 'toArray')) {
            $options = $options->toArray();
        }

        $options = (array) $options;

        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * Proxy call to the hostname validator instance
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->getHostnameValidator(), $method), $args);
    }

    /**
     * Set options
     *
     * @param array $options
     * @return Zefram_Validate_Uri
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . $key;
            if (is_callable(array($this, $method))) {
                $this->{$method}($value);
            }
        }
        return $this;
    }

    /**
     * Retrieve hostname validator
     *
     * @return Zend_Validate_Hostname
     */
    public function getHostnameValidator()
    {
        if ($this->_hostnameValidator === null) {
            $this->_hostnameValidator = new Zend_Validate_Hostname();
        }
        return $this->_hostnameValidator;
    }

    /**
     * @param  string|array $schemes
     * @return Zefram_Validate_Url this object
     */
    public function setAllowedSchemes($schemes)
    {
        if (is_string($schemes) && strpos($schemes, ',') !== false) {
            $schemes = array_map('trim', explode(',', $schemes));
        }
        $this->_allowedSchemes = array_map('strtolower', (array) $schemes);
        return $this;
    }

    /**
     * @return string|array
     */
    public function getAllowedSchemes()
    {
        return $this->_allowedSchemes;
    }

    /**
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);

        try {
            $uri = Zefram_Uri::factory($value);
        } catch (Exception $e) {
            $this->_error(self::NOT_URI);
            return false;
        }

        if (!$uri->valid()) {
            $this->_error(self::NOT_URI);
            return false;
        }

        $this->_scheme = $uri->getScheme();

        if (!in_array($this->_scheme, $this->_allowedSchemes, true)) {
            $this->_error(self::SCHEME_NOT_ALLOWED);
            return false;
        }

        if (!$this->getHostnameValidator()->isValid($uri->getHost())) {
            $this->_error(self::HOSTNAME_NOT_ALLOWED);
            return false;
        }

        return true;
    }
}
