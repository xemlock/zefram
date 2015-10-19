<?php

/**
 * This helper allows controllers to retrieve resources from resource
 * container without directly referencing bootstrap, front controller,
 * or global registry.
 *
 * @author xemlock
 */
class Zefram_Controller_Action_Helper_Resource
    extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Get bootstrap
     *
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     * @throws Zend_Controller_Action_Exception
     */
    public function getBootstrap()
    {
        // All bootstrapped plugin resources have 'bootstrap' param.
        // Zend_Application_Resource_Frontcontroller::init() sets the
        // 'bootstrap' value as a Front Controller parameter. Hence the
        // "official" way of accessing resource container, which is:
        $bootstrap = $this->getFrontController()->getParam('bootstrap');
        if ($bootstrap instanceof Zend_Application_Bootstrap_BootstrapAbstract) {
            return $bootstrap;
        }
        throw new Zend_Controller_Action_Exception('Unable to retrieve application bootstrap');
    }

    /**
     * Retrieve a resource from bootstrap
     *
     * @param  string $resource
     * @param  bool $throw OPTIONAL
     * @return mixed
     * @throws Zend_Controller_Action_Exception
     */
    public function getResource($resource, $throw = true)
    {
        $resource = (string) $resource;
        $bootstrap = $this->getBootstrap();

        if ($bootstrap->hasResource($resource)) {
            return $bootstrap->getResource($resource);
        }

        if ($throw) {
            throw new Zend_Controller_Action_Exception(sprintf(
                'Resource matching "%s" not found',
                $resource
            ));
        }
        return null;
    }

    /**
     * Proxy to {@link getResource()}
     *
     * @param  string $resource
     * @return mixed
     */
    public function direct($resource)
    {
        return $this->getResource($resource);
    }
}
