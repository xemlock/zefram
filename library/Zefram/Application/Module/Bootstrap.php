<?php

/**
 * Base bootstrap class for modules
 *
 * Enhancements:
 * - resource container is shared with parent bootstrap
 * - ambiguity of getApplication() return value is mitigated by accepting only
 *   instances of Zend_Application_Bootstrap_Bootstrapper in setApplication()
 *
 * @category   Zefram
 * @package    Zefram_Application
 * @subpackage Module
 * @author     xemlock
 */
abstract class Zefram_Application_Module_Bootstrap extends Zend_Application_Module_Bootstrap
    implements Zefram_Application_Bootstrap_Bootstrapper
{
    /**
     * Set parent bootstrap
     *
     * @param  Zend_Application_Bootstrap_Bootstrapper $application
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     * @throws Zend_Application_Bootstrap_Exception
     */
    public function setApplication($application)
    {
        // ensures that application is an instance of Bootstrapper only,
        // so that getApplication() does not return application
        if (!$application instanceof Zend_Application_Bootstrap_Bootstrapper) {
            throw new Zend_Application_Bootstrap_Exception(sprintf(
                'Application must be an instance of %s (received "%s" instance)',
                __CLASS__, get_class($application)
            ));
        }

        parent::setApplication($application);

        if ($application instanceof Zend_Application_Bootstrap_BootstrapAbstract) {
            $this->setContainer($application->getContainer());
        }

        return $this;
    }

    /**
     * Is the requested class resource present?
     *
     * @param  string $resource
     * @return bool
     */
    public function hasClassResource($resource)
    {
        return method_exists($this, '_init' . $resource);
    }
}
