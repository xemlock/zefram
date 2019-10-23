<?php

/**
 * Application with a configurable fallback bootstrap class.
 *
 * Additional application configuration options:
 *
 * <pre>
 * bootstrapClass = className
 * </pre>
 *
 * @category Zefram
 * @package  Zefram_Application
 * @author   xemlock
 * @uses     Zend_Application
 */
class Zefram_Application extends Zend_Application
{
    /**
     * @var string
     */
    protected $_bootstrapClass = 'Zefram_Application_Bootstrap_Bootstrap';

    /**
     * @param array $options
     * @return $this
     * @throws Zend_Application_Exception
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (!empty($options['bootstrapClass'])) {
            $this->setBootstrapClass($options['bootstrapClass']);
        }

        return $this;
    }

    /**
     * @param string $class
     * @return $this
     * @throws Zend_Application_Resource_Exception
     */
    public function setBootstrapClass($class)
    {
        $class = (string) $class;
        if (!is_subclass_of($class, 'Zend_Application_Bootstrap_BootstrapAbstract')) {
            throw new Zend_Application_Exception(
                'Bootstrap class class must inherit from Zend_Application_Bootstrap_BootstrapAbstract'
            );
        }
        $this->_bootstrapClass = $class;
        return $this;
    }

    /**
     * Get bootstrap object
     *
     * If no bootstrap is present it will be initialized with an instance of
     * {@link $_bootstrapClass}.
     *
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     */
    public function getBootstrap()
    {
        if (null === $this->_bootstrap) {
            $bootstrapClass = $this->_bootstrapClass;
            $this->_bootstrap = new $bootstrapClass($this);
        }
        return $this->_bootstrap;
    }

    /**
     * Load configuration file of options
     *
     * @param  string $file
     * @throws Zend_Application_Exception When invalid configuration file is provided
     * @return array
     */
    protected function _loadConfig($file)
    {
        // Use Zefram_Config::factory instead of parent implementation, because
        // upon failure to load a config array from .php file, the path to this
        // file is included in the error message
        try {
            return Zefram_Config::factory($file, $this->getEnvironment())->toArray();
        } catch (Exception $e) {
            throw new Zend_Application_Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
