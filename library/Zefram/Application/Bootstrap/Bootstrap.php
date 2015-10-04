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
        // set container class before applying other application options,
        // otherwise registration of any lazy plugin resource will trigger
        // getContainer() which will initialize it with the default class,
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
        if ($resource instanceof Zend_Application_Resource_Resource) {
            return parent::registerPluginResource($resource);
        }

        if (is_object($options) && method_exists($options, 'toArray')) {
            $options = $options->toArray();
        }

        // TODO pluginResources[resource] can be null
        if (is_string($resource)
            && isset($this->_pluginResources[$resource])
            && is_array($this->_pluginResources[$resource])
        ) {
            $options = $this->mergeOptions($this->_pluginResources[$resource], $options);
        }

        // at this point we know whether to use plugin or not - we must
        // use this knowledge here
        // check for correct plugin class here, not when bootstrapping
        $resource = strtolower($resource);
        $pluginClass = $this->getPluginLoader()->load($resource, false);

        if ($pluginClass && (!isset($options['plugin']) || $options['plugin'])) {
            unset($options['plugin']);

            parent::registerPluginResource($resource, $options);

            // NOPE don't mark as not run. Actually FrontController is initialized
            // before modules are analyzed (it is needed for path retrieval)
            // and must not be un-run
            // this is missing step in original impl - when registering new plugin
            // resource it should be marked as not run
            // $this->_unmarkRun($resource);

            // this plugin will be lazily initialized
            // $this->getContainer()->addResourceCallback($resource, array($this, 'bootstrapResource'), array($resource));
            // NOPE - built-in resources are designed to be eagerly initialized,
            // otherwise they will not work correctly (such as controller
            // plugins not being registered)

            // built-in plugin resources require eager initialization, otherwise
            // they must be explicitly bootstrapped before use - since we don't want
            // any significant changes to how bootstrapping is made, we leave this

        } else {
            // mark plugin as run, so that it does not get executed
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
            // TODO module bootstrapping must be done in two steps
            // 1. retrieve module configs and merge them with bootstrap config
            // 2. run all resources in default order
            // Introduces preInit method executed prior to calling init()
            // on plugin resources - this allows for example merging application
            // and modules config
            foreach ($this->_pluginResources as $pluginResource) {
                if (method_exists($pluginResource, 'preInit')) {
                    $pluginResource->preInit();
                }
            }
        }
        parent::_bootstrap($resource);
    }
}
