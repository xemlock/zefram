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
    protected $_containerClass = 'Zend_Registry';

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
     * Set resource container
     *
     * By default, if a resource callback has a non-null return value, this
     * value will be stored in a container using the resource name as the
     * key.
     *
     * Containers must be objects, and must allow setting public properties.
     *
     * If provided container is a string, it is considered as class name that
     * will be used by {@link getContainer()} to instantiate container if no
     * container is present.
     *
     * @param  object|string $container
     * @return $this
     * @throws Zend_Application_Bootstrap_Exception
     */
    public function setContainer($container = null)
    {
        if (null === $container) {
            $this->_container = null;
        } elseif (is_string($container)) {
            $this->_containerClass = $container;
        } else {
            parent::setContainer($container);
        }
        return $this;
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
     * @return $this
     */
    public function setResource($name, $value)
    {
        $resource = strtolower($name);
        $this->getContainer()->{$resource} = $value;
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

    /**
     * Bootstrap and return one or more resources
     *
     * @param  null|string|array $resource
     * @return mixed If resource is given as a string, a resource of matching name is returned,
     *               if given as array, an array of matching resources is returned
     * @throws Zend_Application_Bootstrap_Exception When invalid argument was passed
     */
    protected function _bootstrap($resource = null)
    {
        parent::_bootstrap($resource);

        if ($resource !== null) {
            if (is_array($resource)) {
                return array_map(array($this, 'getResource'), $resource);
            }
            return $this->getResource($resource);
        }
    }
}
