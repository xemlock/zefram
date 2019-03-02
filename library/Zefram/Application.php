<?php

/**
 * Application with a predefined built-in fallback bootstrap instance.
 *
 * @category   Zefram
 * @package    Zefram_Application
 */
class Zefram_Application extends Zend_Application
{
    /**
     * Get bootstrap object
     *
     * If no bootstrap is present it will be initialized with an instance of
     * {@link Zefram_Application_Bootstrap_Bootstrap}.
     *
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     */
    public function getBootstrap()
    {
        if (null === $this->_bootstrap) {
            $this->_bootstrap = new Zefram_Application_Bootstrap_Bootstrap($this);
        }
        return $this->_bootstrap;
    }
}
