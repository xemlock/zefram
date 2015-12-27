<?php

/**
 * Generic URI handler
 *
 * It turns out that Zend_Uri_Http can handle any correct URL, not
 * only those having http(s) scheme, so use it as default class. That
 * is why it is used as a base class for representing URI objects.
 *
 * @category Zefram
 * @package  Zefram_Uri
 * @author   xemlock
 */
abstract class Zefram_Uri extends Zend_Uri_Http
{
    /**
     * Array of valid schemes, provided by subclasses
     *
     * @var array
     */
    protected $_validSchemes = array();

    public function __construct($scheme, $schemeSpecific = '')
    {
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
        if (!in_array($scheme, $this->_validSchemes, true)) {
            throw new Zend_Uri_Exception(sprintf(
                'Scheme "%s" is not valid or is not accepted by %s',
                $scheme,
                get_class($this)
            ));
        }
        $this->_scheme = $scheme;
        return $this;
    }

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

                default:
                    throw new Zend_Uri_Exception(sprintf('Scheme "%s" is not supported', $scheme));
            }
        }
        return parent::factory($uri, $className);
    }
}
