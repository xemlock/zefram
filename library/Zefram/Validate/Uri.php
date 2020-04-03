<?php

/**
 * Zend Framework surprisingly does not provide an URI validator, which should
 * be a part of network related validators (Hostname, Ip and EmailAddress).
 * This class makes up for this shortcoming.
 *
 * @category Zefram
 * @package  Zefram_Validate
 * @author   xemlock
 */
class Zefram_Validate_Uri extends Zend_Validate_Abstract
{
    const INVALID          = 'uriInvalid';
    const NOT_URI          = 'notUri';
    const INVALID_SCHEME   = 'uriInvalidScheme';
    const INVALID_HOSTNAME = 'uriInvalidHostname';

    protected $_messageTemplates = array(
        self::INVALID          => "Invalid type given. String expected",
        self::NOT_URI          => "The input does not appear to be a valid URI",
        self::INVALID_SCHEME   => "URI scheme '%scheme%' is not allowed",
        self::INVALID_HOSTNAME => "The input does not appear to have a valid URI hostname",
    );

    protected $_messageVariables = array(
        'scheme'   => '_scheme',
        'hostname' => '_hostname',
    );

    /**
     * Scheme of currently validated URI, available for external access
     *
     * @var string
     */
    protected $_scheme;

    /**
     * Hostname of currently validated URI, available for external access
     *
     * @var string
     */
    protected $_hostname;

    /**
     * Internal options array
     *
     * @var array
     */
    protected $_options = array(
        'scheme'    => 'Zefram_Uri_Http',
        'allow'     => Zend_Validate_Hostname::ALLOW_ALL,
        'hostname'  => null,
    );

    /**
     * Instantiates URI validator for local use
     *
     * See {@link setOptions()} for the list of supported options.
     *
     * @param array|Zend_Config $options OPTIONAL
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        $this->setOptions((array) $options);
    }

    /**
     * Set options
     *
     * The following option keys are supported:
     * 'scheme'    => List of allowed schemes
     * 'allow'     => Options for the hostname validator, see Zend_Validate_Hostname::ALLOW_*
     * 'hostname'  => A hostname validator, see Zend_Validate_Hostname
     * 'messages'  => Validation messages
     *
     * @param array $options
     * @return Zefram_Validate_Uri Provides fluent interface
     */
    public function setOptions(array $options)
    {
        if (array_key_exists('messages', $options)) {
            $this->setMessages($options['messages']);
        }

        if (array_key_exists('scheme', $options)) {
            $this->setScheme($options['scheme']);
        }

        if (array_key_exists('hostname', $options)) {
            $this->setHostnameValidator($options['hostname']);
        }

        if (array_key_exists('allow', $options)) {
            $this->setAllow($options['allow']);
        }

        return $this;
    }

    /**
     * Set URI scheme handler
     *
     * @param  string $scheme
     * @return Zefram_Validate_Uri Provides fluent interface
     */
    public function setScheme($scheme)
    {
        $this->_options['scheme'] = (string) $scheme;
        return $this;
    }

    /**
     * Retrieve URI scheme handler
     *
     * @return array
     */
    public function getScheme()
    {
        return $this->_options['scheme'];
    }

    /**
     * @param int $allow
     * @return Zefram_Validate_Uri Provides fluent interface
     */
    public function setAllow($allow)
    {
        $this->_options['allow'] = (int) $allow;
        if (isset($this->_options['hostname'])) {
            $this->getHostnameValidator()->setAllow($allow);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getAllow()
    {
        return $this->_options['allow'];
    }

    /**
     * Set hostname validator
     *
     * @param Zend_Validate_Hostname $hostnameValidator
     * @param int $allow
     * @return Zefram_Validate_Uri Provides fluent interface
     */
    public function setHostnameValidator(Zend_Validate_Hostname $hostnameValidator = null, $allow = null)
    {
        $this->_options['hostname'] = $hostnameValidator;

        if ($allow !== null) {
            $this->setAllow($allow);
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
        if (!isset($this->_options['hostname'])) {
            $this->_options['hostname'] = new Zefram_Validate_Hostname($this->getAllow());
        }
        return $this->_options['hostname'];
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
            $uri = Zefram_Uri::factory($value, $this->getScheme());
        } catch (Exception $e) {
            $this->_error(self::NOT_URI);
            return false;
        }

        if (!$uri->valid()) {
            $this->_error(self::NOT_URI);
            return false;
        }

        $this->_scheme = $uri->getScheme();
        $this->_hostname = $uri->getHost();

        if (strlen($this->_hostname)) {
            $hostnameValidator = $this->getHostnameValidator();
            $hostnameValidator->setTranslator($this->getTranslator());

            if (!$hostnameValidator->isValid($this->_hostname)) {
                $this->_error(self::INVALID_HOSTNAME);

                // Get messages and errors from hostnameValidator
                foreach ($hostnameValidator->getMessages() as $code => $message) {
                    $this->_messages[$code] = $message;
                }
                foreach ($hostnameValidator->getErrors() as $error) {
                    $this->_errors[] = $error;
                }

                return false;
            }
        }

        return true;
    }
}
