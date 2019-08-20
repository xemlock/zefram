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
            throw new Zend_Application_Resource_Exception(
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
}
