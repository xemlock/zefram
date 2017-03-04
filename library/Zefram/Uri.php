<?php

/**
 * Generic URI handler
 *
 * It turns out that Zend_Uri_Http can handle any correct URL, not
 * only those having http(s) scheme, so use it as default class. That
 * is why it is used as a base class for representing URI objects.
 *
 * URI construction has been simplified, constructors need not to be
 * protected and can be called directly. Scheme specific part does not
 * need to be separated from scheme.
 *
 * @category Zefram
 * @package  Zefram_Uri
 * @author   xemlock
 */
class Zefram_Uri extends Zend_Uri_Http
{
    /**
     * Array of valid schemes, provided by subclasses
     *
     * @var array
     */
    protected $_validSchemes = array();

    /**
     * Create a new URI object
     *
     * @param string $scheme
     * @param string $schemeSpecific
     */
    public function __construct($scheme, $schemeSpecific = '')
    {
        if (strpos($scheme, ':') !== false) {
            list($scheme, $schemeSpecific) = explode(':', $scheme, 2);
        }
        $this->setScheme($scheme);
        parent::__construct($scheme, $schemeSpecific);
    }

    /**
     * Set the URI scheme
     *
     * @param string $scheme
     * @return Zefram_Uri Provides fluent interface
     * @throws Zend_Uri_Exception
     */
    public function setScheme($scheme)
    {
        $scheme = strtolower($scheme);

        if (!$this->validateScheme($scheme)) {
            throw new Zend_Uri_Exception(sprintf('Scheme "%s" is not valid or not accepted', $scheme));
        }

        $this->_scheme = $scheme;
        return $this;
    }

    /**
     * Returns true if and only if the scheme string passes validation.
     *
     * @param  string $scheme
     * @return bool
     */
    public function validateScheme($scheme)
    {
        if (count($this->_validSchemes)
            && !in_array(strtolower($scheme), $this->_validSchemes, true)
        ) {
            return false;
        }
        return (bool) preg_match('/^[A-Za-z][A-Za-z0-9\-\.+]*$/', $scheme);
    }

    public function validateHost($host = null)
    {
        if ($host === null) {
            $host = $this->_host;
        }

        // If the host is empty, then it is considered valid
        if (strlen($host) === 0) {
            return true;
        }

        return parent::validateHost($host);
    }

    /**
     * Validate the current URI from the instance variables. Returns true if and only if all
     * parts except path pass validation.
     *
     * Path validation is delegated to URI subclasses.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->validateUsername()
            && $this->validatePassword()
            && $this->validateHost()
            && $this->validatePort()
            && $this->validateQuery()
            && $this->validateFragment();
    }

    protected function _parseUri($schemeSpecific)
    {
        // According to RFC 2396, if scheme specific part does not start with
        // double slash, it means that the authority component is absent.
        // Unfortunately, the original _parseUri() method fails to handle such
        // case, hence the updated implementation below.

        if (substr($schemeSpecific, 0, 2) === '//') {
            return parent::_parseUri($schemeSpecific);
        }

        // High-level decomposition parser
        $pattern = '~^(?<path>[^?#]*)(\?(?<query>[^#]*))?(#(?<fragment>.*))?$~';
        $status  = @preg_match($pattern, $schemeSpecific, $matches);
        if ($status === false) {
            throw new Zend_Uri_Exception('Internal error: scheme-specific decomposition failed');
        }

        // Save URI components that need no further decomposition
        $this->_path     = $matches['path'];
        $this->_query    = isset($matches['query']) ? $matches['query'] : null;
        $this->_fragment = isset($matches['fragment']) ? $matches['fragment'] : null;

        // Authority component is absent
        $this->_username = '';
        $this->_password = '';
        $this->_host     = '';
        $this->_port     = '';
    }

    /**
     * @param string $uri
     * @param string $className OPTIONAL
     * @return Zefram_Uri
     * @throws Zend_Uri_Exception
     */
    public static function factory($uri = 'http', $className = null)
    {
        if ($className === null) {
            list($scheme, ) = explode(':', $uri, 2);
            $scheme = strtolower($scheme);

            switch ($scheme) {
                case 'file':
                    $className = 'Zefram_Uri_File';
                    break;

                case 'http':
                case 'https':
                    $className = 'Zefram_Uri_Http';
                    break;

                case 'mailto':
                    $className = 'Zefram_Uri_Mailto';
                    break;
            }
        }
        return parent::factory($uri, $className);
    }
}
