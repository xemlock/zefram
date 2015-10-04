<?php

/**
 * @category   Zefram
 * @package    Zefram_Application
 * @subpackage Bootstrap
 * @author     xemlock <xemlock@gmail.com>
 */
class Zefram_Application_Bootstrap_Bootstrap
    extends Zend_Application_Bootstrap_Bootstrap
    implements Zefram_Application_Bootstrap_Bootstrapper
{
    /**
     * Default resource container class.
     *
     * @var string
     */
    protected $_containerClass = 'Zefram_Application_ResourceContainer';

    /**
     * {@inheritDoc}
     *
     * @param Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
     */
    public function __construct($application)
    {
        // container class must be set before applying other application
        // options (e.g. in parent constructor), otherwise registration of any
        // unrecognized plugin resource will trigger getContainer(), which
        // will initialize the container using the default class,
        // (from $_containerClass property), and not from application options.

        $options = $application->getOptions();

        if (isset($options['bootstrap']['containerClass'])) {
            $this->_containerClass = $options['bootstrap']['containerClass'];
        }

        parent::__construct($application);
    }

    /**
     * Get the plugin loader for resources.
     *
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader()
    {
        if ($this->_pluginLoader === null) {
            $prefixPaths = array(
                'Zefram_Application_Resource_'  => 'Zefram/Application/Resource/',
            );
            foreach ($prefixPaths as $prefix => $path) {
                parent::getPluginLoader()->addPrefixPath($prefix, $path);
            }
        }
        return $this->_pluginLoader;
    }

    /**
     * Retrieve resource container.
     *
     * If no container is present a new container is created. Container class
     * can be configured via 'containerClass' option under 'bootstrap' section.
     *
     * @return object
     */
    public function getContainer()
    {
        if (null === $this->_container) {
            $containerClass = $this->_containerClass;
            $container = new $containerClass();
            $this->setContainer($container);
        }
        return $this->_container;
    }

    /**
     * Save given resource using a custom name without involving _init method
     * or plugin mechanism.
     *
     * @param  string $name
     * @param  mixed $value
     * @return Zefram_Application_Bootstrap_Bootstrap
     */
    public function setResource($name, $value)
    {
        $resource = strtolower($name);
        $this->getContainer()->{$resource} = $value;
        return $this;
    }

    /**
     * Is the requested class resource registered?
     *
     * @param  string $resource
     * @return bool
     */
    public function hasClassResource($resource)
    {
        return method_exists($this, '_init' . $resource);
    }

    /**
     * Register a new resource plugin
     *
     * If a resource spec (an array, not plugin instance) of the same name is
     * already registered, merge it with the provided one. This allows modules
     * to modify resources defined elsewhere. To completely overwrite an
     * existing resource spec with the provided one, use
     * {@link unregisterPluginResource()} first.
     *
     * If resource name is not a valid plugin resource and a 'class' option is
     * provided, resource will be copied as is to resource container. It is
     * up to Resource Container to process such a resource accordingly.
     *
     * @param  string|Zend_Application_Resource_Resource $resource
     * @param  mixed $options
     * @return Zefram_Application_Bootstrap_Bootstrap
     * @throws Zend_Application_Bootstrap_Exception When invalid resource is provided
     */
    public function registerPluginResource($resource, $options = null)
    {
        if ($resource instanceof Zend_Application_Resource_Resource) {
            return parent::registerPluginResource($resource);
        }

        $resource = strtolower($resource);

        // prevent registering unrunnable plugin resources
        if (in_array($resource, $this->_run)) {
            throw new Zend_Application_Bootstrap_Exception(sprintf(
                "Resource '%s' has already been ran", $resource
            ));
        }

        // Check whether to use plugin resource or to put the resource
        // directly in the container for deferred instantiation.

        // Resources that are not recognized as plugin resources need not to
        // be bootstrapped. The idea here is that these resources will be
        // bootstrapped by the resource container upon explicit request.

        // Instantiation of such resources must be done at the stage of
        // plugin registration (in registerPluginResource()) and not in
        // _loadPluginResource(), to avoid explicit bootstrapping.

        if (($pluginClass = $this->getPluginLoader()->load($resource, false))
            && (!isset($options['plugin']) || $options['plugin'])
        ) {
            unset($options['plugin']);
            parent::registerPluginResource($resource, $options);

        } else {
            // have to write directly to _pluginResources array because
            // registerPluginResource() mangles the resource name when
            // the resource is provided as an already instantiated object
            $this->_pluginResources[$resource] = new Zefram_Application_Resource_ContainerData(array(
                'container' => $this->getContainer(),
                'containerKey' => $resource,
                'data' => $options,
            ));
        }

        return $this;
    }

    // TODO public function unregisterPluginResource -> handle ContainerData resources

    /**
     * {@inheritDoc}
     */
    protected function _bootstrap($resource = null)
    {
        if (null === $resource) {
            // Before bootstrapping resources try to call preInit() method on
            // all registered plugin resources
            foreach ($this->_pluginResources as $pluginResource) {
                if (method_exists($pluginResource, 'preInit')) {
                    $pluginResource->preInit();
                }
            }
        }
        parent::_bootstrap($resource);
    }
}
