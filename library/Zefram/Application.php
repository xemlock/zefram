<?php

class Zefram_Application extends Zend_Application
{
    /**
     * Set bootstrap path/class
     *
     * @param string $path  If the $class parameter is empty and $path is a
     *                      valid class name, it will be used as the bootstrap
     *                      class
     * @param string $class If given it will be used as the bootstrap class
     *                      name, otherwise a default 'Bootstrap' class name
     *                      will be used
     * @return Zefram_Application
     */
    public function setBootstrap($path, $class = null)
    {
        if (null === $class) {
            if (class_exists($path, false)) {
                $class = $path;
                $path = null;
            }
        }
        return parent::setBootstrap($path, $class);
    }

    /**
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
