<?php

/**
 * Base bootstrap class for modules
 *
 * Differences from the Zend_Application_Module_Bootstrap:
 * - application passed to {@link setApplication()} can only be an instance of
 *   Zend_Application_Bootstrap_BootstrapAbstract, not Zend_Application, to
 *   ensure that retrieving a bootstrapped resource is always available.
 *   Consequently, {@link getApplication()} always returns an instance of
 *   Zend_Application_Bootstrap_BootstrapAbstract
 * - plugin loader and resource loader are set whenever application is set, not
 *   only in constructor, preventing bootstrap from falling into an incoherent
 *   state
 * - bootstrap options are extracted from application options, but the key name
 *   is case-insensitively matched. All keys that match module name are merged
 *   into a single array
 *
 * Retrieving module resources in action controllers:
 *
 * <pre>
 * $resource = $this->getInvokeArg('bootstrap')
 *      ->getResource('modules')
 *      ->offsetGet('modulename')->getResource('resource');
 * </pre>
 *
 * or anywhere in the application:
 *
 * <pre>
 * $resource = Zend_Controler_Front::getInstance()->getParam('bootstrap')
 *      ->getResource('modules')
 *      ->offsetGet('modulename')->getResource('resource');
 * </pre>
 *
 * @category   Zefram
 * @package    Zefram_Application
 * @subpackage Module
 * @author     xemlock
 *
 * @method Zend_Application_Bootstrap_BootstrapAbstract getApplication()
 * @method $this bootstrap(string|string[] $resource = null)
 */
abstract class Zefram_Application_Module_Bootstrap extends Zend_Application_Module_Bootstrap
    implements Zefram_Application_Bootstrap_Bootstrapper
{
    /**
     * Path to directory this module resides in. Set it explicitly to reduce
     * impact of determining it.
     * @var string
     */
    protected $_moduleDirectory;

    /**
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

        // Set module options
        $options = array();
        foreach ($application->getOptions() as $key => $value) {
            if (!strcasecmp($key, $this->getModuleName())) {
                $options = $this->mergeOptions($options, $value);
            }
        }
        $this->setOptions($options);

        // ZF-6545: ensure front controller is registered
        if (!$this->hasPluginResource('FrontController')) {
            $this->registerPluginResource('FrontController');
        }

        // ZF-6545: prevent recursive registration of modules
        if ($this->hasPluginResource('modules')) {
            $this->unregisterPluginResource('modules');
        }
    }

    /**
     * Set parent bootstrap
     *
     * @param  Zend_Application_Bootstrap_BootstrapAbstract $application
     * @return $this
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

        return $this;
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
     * Retrieve path to directory this module resides in
     *
     * @return string
     */
    public function getModuleDirectory()
    {
        if (null === $this->_moduleDirectory) {
            $reflectionClass = new ReflectionClass($this);
            $this->_moduleDirectory = dirname($reflectionClass->getFileName());
        }
        return $this->_moduleDirectory;
    }
}
