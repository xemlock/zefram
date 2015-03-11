<?php

/**
 * @category   Zefram
 * @package    Zefram_Application
 * @subpackage Bootstrap
 * @version    2015-03-11
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
    public function setOptions(array $options) // {{{
    {
        parent::setOptions($options);

        if (isset($this->_options['bootstrap']['containerClass'])) {
            $this->_containerClass = $this->_options['bootstrap']['containerClass'];
        }

        return $this;
    } // }}}

    /**
     * Get the plugin loader for resources.
     *
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader() // {{{
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
    } // }}}

    /**
     * Retrieve resource container.
     *
     * If no container is present a new container is created. Container class
     * can be configured via 'containerClass' option under 'bootstrap' section.
     *
     * @return object
     */
    public function getContainer() // {{{
    {
        if (null === $this->_container) {
            $containerClass = $this->_containerClass;
            $container = new $containerClass;
            $this->setContainer($container);
        }
        return $this->_container;
    } // }}}

    /**
     * Save given resource using a custom name without involving _init method
     * or plugin mechanism.
     *
     * @param  string $name
     * @param  mixed $value
     * @return Zefram_Application_Bootstrap_Bootstrap
     */
    public function setResource($name, $value) // {{{
    {
        $resource = strtolower($name);

        if ($this->hasResource($resource)) {
            throw new Zend_Application_Bootstrap_Exception(sprintf(
                "Resource '%s' already exists", $resource
            ));
        }

        $this->getContainer()->{$resource} = $value;
        return $this;
    } // }}}

    /**
     * Is the requested class resource registered?
     *
     * @param  string $resource
     * @return bool
     */
    public function hasClassResource($resource) // {{{
    {
        return method_exists($this, '_init' . $resource);
    } // }}}

    /**
     * Loads a plugin resource.
     *
     * If resource name corresponds to a valid plugin resource, load it, unless
     * a 'plugin' = false option is provided. If resource name is not a valid
     * plugin resource and a 'class' option is provided, resource is treated
     * as a resource definition and will be wrapped in a
     * (@see Zefram_Application_Resource_ResourceDefinition) plugin instance.
     *
     * Resource defitions must be supported by resource container to work
     * as intended.
     *
     * @param  string $resource
     * @param  array|object|null $options
     * @return string|false
     */
    protected function _loadPluginResource($resource, $options = null) // {{{
    {
        if (is_object($options) && method_exists($options, 'toArray')) {
            $options = $options->toArray();
        }
        $options = (array) $options;

        $resource = strtolower($resource);
        $pluginClass = $this->getPluginLoader()->load($resource, false);

        if ($pluginClass && (!isset($options['plugin']) || $options['plugin'])) {
            unset($options['plugin']);
            return parent::_loadPluginResource($resource, $options);
        }

        if (isset($options['class'])) {
            $this->_pluginResources[$resource] = new Zefram_Application_Resource_ResourceDefinition($options);
            return $resource;
        }

        return false;
    } // }}}
}
