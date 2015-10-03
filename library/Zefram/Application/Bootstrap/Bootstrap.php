<?php

/**
 * @category   Zefram
 * @package    Zefram_Application
 * @subpackage Bootstrap
 * @version    2015-10-04
 * @author     xemlock
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
     * Configure this instance.
     *
     * @param  array $options
     * @return Zefram_Application_Bootstrap_Bootstrap
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (isset($this->_options['bootstrap']['containerClass'])) {
            $this->_containerClass = $this->_options['bootstrap']['containerClass'];
        }

        return $this;
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
            $container = new $containerClass;
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

        if ($this->hasResource($resource)) {
            throw new Zend_Application_Bootstrap_Exception(sprintf(
                "Resource '%s' already exists", $resource
            ));
        }

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
        if (is_object($options) && method_exists($options, 'toArray')) {
            $options = $options->toArray();
        }

        // check if a resource spec of the same name is already registered,
        // if so, merge it with the provided spec
        if (is_string($resource)
            && isset($this->_pluginResources[$resource])
            && is_array($this->_pluginResources[$resource])
            && is_array($options)
        ) {
            $options = $this->mergeOptions($this->_pluginResources[$resource], $options);
        }

        // at this point we know whether to use plugin or not - we must
        // use this knowledge here
        // check for correct plugin class here, not when bootstrapping
        $resource = strtolower($resource);
        $pluginClass = $this->getPluginLoader()->load($resource, false);

        // mark plugin as run, so that it does not get executed

        if ($pluginClass && (!isset($options['plugin']) || $options['plugin'])) {
            unset($options['plugin']);
            parent::registerPluginResource($resource, $options);
        } else {
            // this is not a plugin resource - ok, add it right away to resource
            // container, mark corresponding resource as executed
            // options can be of any type - its up to container to interpret it
            $this->getContainer()->{$resource} = $options;
            $this->_markRun($resource);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function _bootstrap($resource = null)
    {
        if (null === $resource) {
            // configure modules before bootstrapping resources
            $moduleManager = $this->getPluginResource('modules');
            if ($moduleManager && method_exists($moduleManager, 'configureModules')) {
                $moduleManager->configureModules();
            }
        }
        parent::_bootstrap($resource);
    }
}
