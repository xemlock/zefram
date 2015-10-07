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
     * @var Zend_Application_Bootstrap_BootstrapAbstract
     */
    protected $_container;

    /**
     * Set resource container
     *
     * @param object $container
     * @throws Zend_Controller_Action_Exception
     */
    public function setContainer($container)
    {
        if (!is_object($container)) {
            throw new Zend_Controller_Action_Exception('Resource container must be an object');
        }
        $this->_container = $container;
        return $this;
    }

    /**
     * Get resource container
     *
     * @return object
     */
    public function getContainer()
    {
        if (empty($this->_container)) {
            // All bootstrapped plugin resources have 'bootstrap' param.
            // Zend_Application_Resource_Frontcontroller::init() sets the
            // 'bootstrap' value as a Front Controller param. Hence the
            // "official" way of accessing resource container.
            $bootstrap = $this->getFrontController()->getParam('bootstrap');
            if ($bootstrap instanceof Zend_Application_Bootstrap_BootstrapAbstract) {
                $container = $bootstrap->getContainer();
            }
            // if no container was retrieved from bootstrap (or there was no
            // bootstrap at all) fall back to Zend_Registry instance.
            if (empty($container)) {
                $container = Zend_Registry::getInstance();
            }
            $this->setContainer($container);
        }
        return $this->_container;
    }

    /**
     * Retrieve resource from container
     *
     * @param  string $resource
     * @return mixed
     * @throws Zend_Controller_Action_Exception
     */
    public function getResource($resource)
    {
        $resource = (string) $resource;
        $container = $this->getContainer();

        // Check if container is compatible with container-interop API
        // (https://github.com/container-interop/container-interop)
        if (method_exists($container, 'get')) {
            return $container->get($resource);
        } elseif (isset($container->{$resource})) {
            return $container->{$resource};
        }

        throw new Zend_Controller_Action_Exception(sprintf(
            'Resource matching "%s" not found',
            $resource
        ));
    }

    /**
     * Proxy to {@link getResource()}.
     *
     * @param  string $resource
     * @return mixed
     */
    public function direct($resource)
    {
        return $this->getResource($resource);
    }
}
