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
        // Container class option must be set before any other options,
        // otherwise any call to getContainer() will initialize the
        // container using the default class (from $this->_containerClass),
        // not from application options.

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

    protected $_rawResources = array();


    /**
     * Register a new resource plugin
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
        if (!is_string($resource)) {
            return parent::registerPluginResource($resource, $options);
        }

        $resource = strtolower($resource);

        // Check whether to use plugin resource or to put the resource
        // directly in the container for deferred instantiation.

        // Resources that are not recognized are inserted to container as-is
        // upon resource registration

        if (($pluginClass = $this->getPluginLoader()->load($resource, false))
            && (!isset($options['plugin']) || $options['plugin'])
        ) {
            unset($options['plugin']);
            parent::registerPluginResource($resource, $options);

        } elseif (!in_array($resource, $this->getClassResourceNames())) {
            // these are not plugin resources
            // don't add if overwriting _init method exists
            // otherwise add to rawResources array
            $this->_rawResources[$resource] = $options;
        }

        return $this;
    }

    protected function _executeRawResources()
    {
        foreach ($this->_rawResources as $res => $value) {
            $this->getContainer()->{$res} = $value;
            unset($this->_rawResources[$res]);
        }
    }

    protected function _bootstrap($resource = null)
    {
        if ($resource === null) {
            if ($this->hasPluginResource('modules')) {
                $modules = $this->getPluginResource('modules');
                $modules->preInit(); // to powinno zaktualizować config
            }
            $this->_executeRawResources();
        }
        parent::_bootstrap($resource);
    }
}
