<?php

/**
 * Base bootstrap class for modules
 *
 * Differences from the Zend_Application_Module_Bootstrap:
 * - application can only be an instance of Zend_Application_Bootstrap_BootstrapAbstract,
 *   not Zend_Application, to make sure resource bootstrapping and retrieval is always
 *   available
 * - plugin loader and resource loader are set whenever application is set, not only
 *   in constructor
 * - resource container is shared with parent bootstrap
 * - front controller plugin resource is registered in parent bootstrap, if it is
 *   not already present there
 *
 * @category   Zefram
 * @package    Zefram_Application
 * @subpackage Module
 * @author     xemlock
 */
abstract class Zefram_Application_Module_Bootstrap extends Zend_Application_Module_Bootstrap
{
    /**
     * Constructor
     *
     * @param Zend_Application|Zend_Application_Bootstrap_BootstrapAbstract $application
     */
    public function __construct($application)
    {
        if ($application instanceof Zend_Application) {
            $application = $application->getBootstrap();
        }
        $this->setApplication($application);

        // Set only module options
        $key = strtolower($this->getModuleName());
        if ($application->hasOption($key)) {
            $this->setOptions($application->getOption($key));
        }

        // ZF-6545: ensure front controller is registered in parent bootstrap
        if (!$this->getApplication()->hasPluginResource('FrontController')) {
            $this->getApplication()->registerPluginResource('FrontController');
        }
    }

    /**
     * Set parent bootstrap
     *
     * @param  Zend_Application_Bootstrap_BootstrapAbstract $application
     * @return Zefram_Application_Module_Bootstrap
     * @throws Zend_Application_Bootstrap_Exception
     */
    public function setApplication($application)
    {
        // Ensure that application is an instance of BootstrapAbstract only
        if (!$application instanceof Zend_Application_Bootstrap_BootstrapAbstract) {
            throw new Zend_Application_Bootstrap_Exception(sprintf(
                'Application must be an instance of %s (received "%s" instance)',
                __CLASS__, get_class($application)
            ));
        }

        parent::setApplication($application);

        // Use same plugin loader as parent bootstrap
        $this->setPluginLoader($application->getPluginLoader());

        // Use same resource loader as parent bootstrap
        if ($application->hasOption('resourceloader')) {
            $this->setResourceLoader($application->getOption('resourceloader'));
        }
        $this->initResourceLoader();

        // Use same container as parent bootstrap
        $this->setContainer($application->getContainer());

        return $this;
    }

    /**
     * Retrieve parent bootstrap instance
     *
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     */
    public function getApplication()
    {
        return $this->_application;
    }
}
