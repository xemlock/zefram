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
 * - resource bootstrapping and retrieving with one method call
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
     * Constructor
     *
     * @param Zend_Application|Zend_Application_Bootstrap_BootstrapAbstract $application
     */
    public function __construct($application)
    {
        // Don't call parent::__construct() as this may cause duplicated
        // (or even recursive) initialization of resources, see ZF-6545:
        // http://framework.zend.com/issues/browse/ZF-6545

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

    /**
     * Is the requested class resource present
     *
     * @param  string $resource
     * @return bool
     */
    public function hasClassResource($resource)
    {
        $resource = strtolower($resource);

        return array_key_exists($resource, $this->getClassResources());
    }

    /**
     * Bootstrap and retrieve resource
     *
     * If a resource is not registered in this bootstrap, the parent bootstrap
     * (or application) is examined.
     *
     * This function is roughly equivalent to calling {@link bootstrap()} followed
     * by {@link getResource()}. So instead of writing:
     * <code>
     * $frontController = $this->bootstrap('FrontController')->getResource('FrontController');
     * </code>
     *
     * resource can be bootstrapped and retrieved in a single method call:
     * <code>
     * $frontController = $this->bootstrapResource('FrontController');
     * </code>
     *
     * @param string $resource Resource name
     * @return mixed Bootstrapped resource (if any)
     * @throws Zend_Application_Bootstrap_Exception When matching resource was not found
     */
    public function bootstrapResource($resource)
    {
        $resource = strtolower($resource);

        /** @var Zend_Application_Bootstrap_BootstrapAbstract[] $bootstraps */
        $bootstraps = array($this, $this->getApplication());

        foreach ($bootstraps as $bootstrap) {
            if ($bootstrap->hasPluginResource($resource) ||
                array_key_exists($resource, $bootstrap->getClassResources())
            ) {
                $bootstrap->bootstrap($resource);
                return $bootstrap->getResource($resource);
            }
        }

        throw new Zend_Application_Bootstrap_Exception('Resource matching "' . $resource . '" not found');
    }

    /**
     * Bootstrap and retrieve one or more resources
     *
     * Example usage:
     * <code>
     * list($frontController, $view) = $this->bootstrapResources('FrontController', 'View');
     * </code>
     *
     * @param array|string $resources,... If an array is passed, it's treated as a list of
     *                                    resource names, otherwise the list of resource names
     *                                    will be constructed from all the passed in parameters
     * @return array Array with bootstrapped resources, in the same order as specified in the
     *               provided list of resource names
     * @throws Zend_Application_Bootstrap_Exception When matching resource was not found
     */
    public function bootstrapResources($resources)
    {
        if (!is_array($resources)) {
            $resources = func_get_args();
        }

        $bootstrappedResources = array();

        foreach ($resources as $resource) {
            $bootstrappedResources[] = $this->bootstrapResource($resource);
        }

        return $bootstrappedResources;
    }
}
