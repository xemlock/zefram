<?php

/**
 * Filter chain.
 *
 * A replacement for {@link Zend_Filter} allowing filters to be passed the
 * same way as they are to {@link Zend_Form_Element::addFilters()}.
 *
 * Differences from Zend_Filter:
 * - Multiple filters of the same class name are allowed
 * - Multiple filters can be added in a single function call
 * - Filter can be specified either as string, array or a Zend_Filter instance
 *
 * @category Zefram
 * @package  Zefram_Filter
 * @author   xemlock
 * @uses     Zend_Filter
 * @uses     Zend_Loader
 */
class Zefram_Filter implements Zend_Filter_Interface
{
    const CHAIN_APPEND  = Zend_Filter::CHAIN_APPEND;
    const CHAIN_PREPEND = Zend_Filter::CHAIN_PREPEND;

    /**
     * Filter chain
     *
     * @var array
     */
    protected $_filters = array();

    /**
     * @var Zend_Loader
     */
    protected $_pluginLoader;

    /**
     * @param array|Zend_Config $options
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            if (is_object($options) && method_exists($options, 'toArray')) {
                $options = $options->toArray();
            }

            $options = (array) $options;

            // set loader before loading any filters

            if (isset($options['pluginLoader'])) {
                $this->setPluginLoader($options['pluginLoader']);
                unset($options['pluginLoader']);
            }

            // add prefix paths before loading any filters

            if (isset($options['prefixPaths'])) {
                $this->addPrefixPaths($options['prefixPaths']);
                unset($options['prefixPaths']);
            }

            foreach ($options as $key => $value) {
                $method = 'set' . $key;
                if (method_exists($this, $method)) {
                    $this->$method($value);
                    unset($options[$key]);
                }
            }

            if (isset($options['filters'])) {
                $this->addFilters($options['filters']);

            } elseif ($options) {
                // treat any remaining options as filters, if no explicit
                // 'filters' option is provided
                $this->addFilters($options);
            }
        }
    }

    /**
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader()
    {
        if (null === $this->_pluginLoader) {
            $this->_pluginLoader = new Zend_Loader_PluginLoader(array(
                'Zend_Filter_'   => 'Zend/Filter/',
                'Zefram_Filter_' => 'Zefram/Filter/',
            ));
        }
        return $this->_pluginLoader;
    }

    /**
     * @param Zend_Loader_PluginLoader_Interface $loader
     * @return $this
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader)
    {
        $this->_pluginLoader = $loader;
        return $this;
    }

    /**
     * @param string $prefix
     * @param string $path
     * @return $this
     */
    public function addPrefixPath($prefix, $path)
    {
        $this->getPluginLoader()->addPrefixPath($prefix, $path);
        return $this;
    }

    /**
     * @param array $spec
     * @return $this
     */
    public function addPrefixPaths(array $spec)
    {
        foreach ($spec as $prefix => $path) {
            if (is_array($path)) {
                if (isset($path['prefix']) && isset($path['path'])) {
                    $this->addPrefixPath($path['prefix'], $path['path']);
                }
            } elseif (is_string($prefix)) {
                $this->addPrefixPath($prefix, $path);
            }
        }
        return $this;
    }

    /**
     * @param array $filters
     * @return $this
     * @throws Zend_Filter_Exception
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $filterInfo) {
            if (is_string($filterInfo)) {
                $this->addFilter($filterInfo);
            } elseif ($filterInfo instanceof Zend_Filter_Interface) {
                $this->addFilter($filterInfo);
            } elseif (is_array($filterInfo)) {
                $argc = count($filterInfo);
                $options = array();
                if (isset($filterInfo['filter'])) {
                    $filter = $filterInfo['filter'];
                    if (isset($filterInfo['options'])) {
                        $options = $filterInfo['options'];
                    }
                    $this->addFilter($filter, $options);
                } else {
                    switch (true) {
                        case (0 == $argc):
                            break;
                        case (1 <= $argc):
                            $filter  = array_shift($filterInfo);
                        case (2 <= $argc):
                            $options = array_shift($filterInfo);
                        default:
                            $this->addFilter($filter, $options);
                            break;
                    }
                }
            } else {
                throw new Zend_Filter_Exception('Invalid filter passed to addFilters()');
            }
        }
        return $this;
    }

    /**
     * Adds a filter to the chain
     *
     * @param  Zend_Filter_Interface|string $filter
     * @param  string|array $placement OPTIONAL
     * @param  array $options OPTIONAL
     * @return $this
     * @throws Zend_Filter_Exception
     */
    public function addFilter($filter, $placement = self::CHAIN_APPEND, array $options = array())
    {
        if (is_array($placement)) {
            $options = $placement;
            $placement = self::CHAIN_APPEND;
        }
        if ($filter instanceof Zend_Filter_Interface) {
            // pass
        } elseif (is_string($filter)) {
            $filter = array(
                'filter'  => $filter,
                'options' => $options,
            );
        } else {
            throw new Zend_Filter_Exception('Invalid filter provided to addFilter; must be string or Zend_Filter_Interface');
        }
        if ($placement == self::CHAIN_PREPEND) {
            array_unshift($this->_filters, $filter);
        } else {
            $this->_filters[] = $filter;
        }
        return $this;
    }

    /**
     * Add a filter to the end of the chain
     *
     * @param  Zend_Filter_Interface $filter
     * @return $this
     */
    public function appendFilter($filter)
    {
        return $this->addFilter($filter, self::CHAIN_APPEND);
    }

    /**
     * Add a filter to the start of the chain
     *
     * @param  Zend_Filter_Interface $filter
     * @return $this
     */
    public function prependFilter($filter)
    {
        return $this->addFilter($filter, self::CHAIN_PREPEND);
    }

    /**
     * Get all the filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array();
        foreach ($this->_filters as $key => $filter) {
            if (!$filter instanceof Zend_Filter_Interface) {
                $filter = $this->_filters[$key] = $this->_loadFilter($filter);
            }
            $filters[] = $filter;
        }
        return $filters;
    }

    /**
     * @param string|int $name
     * @return bool|Zend_Filter_Interface
     * @throws Zend_Filter_Exception
     */
    public function getFilter($name)
    {
        if (!isset($this->_filters[$name])) {
            $len = strlen($name);
            foreach ($this->_filters as $key => $filter) {
                $localName = $filter instanceof Zend_Filter_Interface ? get_class($filter) : $filter['filter'];

                if ($len > strlen($localName)) {
                    continue;
                }

                if (0 === substr_compare($localName, $name, -$len, $len, true)) {
                    if (is_array($filter)) {
                        $this->_filters[$key] = $this->_loadFilter($filter);
                    }
                    return $this->_filters[$key];
                }
            }
            return false;
        }

        if (is_array($this->_filters[$name])) {
            $this->_filters[$name] = $this->_loadFilter($this->_filters[$name]);
        }

        return $this->_filters[$name];
    }

    /**
     * Lazy-load a filter
     *
     * @param  array $filter
     * @return Zend_Filter_Interface
     */
    protected function _loadFilter(array $filter)
    {
        $origName = $filter['filter'];
        $name = $this->getPluginLoader()->load($filter['filter']);

        if (array_key_exists($name, $this->_filters)) {
            throw new Zend_Form_Exception(sprintf('Filter instance already exists for filter "%s"', $origName));
        }

        if (empty($filter['options'])) {
            $instance = new $name;
        } else {
            $r = new ReflectionClass($name);
            if ($r->hasMethod('__construct')) {
                $instance = $r->newInstanceArgs((array) $filter['options']);
            } else {
                $instance = $r->newInstance();
            }
        }

        return $instance;
    }

    /**
     * Returns $value filtered through each filter in the chain
     *
     * Filters are run in the order in which they were added to the chain (FIFO)
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        $valueFiltered = $value;
        foreach ($this->getFilters() as $filter) {
            $valueFiltered = $filter->filter($valueFiltered);
        }
        return $valueFiltered;
    }

    /**
     * Returns the set default namespaces
     *
     * Proxy to {@link Zend_Filter::getDefaultNamespaces()}
     *
     * @return array
     */
    public static function getDefaultNamespaces()
    {
        return Zend_Filter::getDefaultNamespaces();
    }

    /**
     * Sets new default namespaces
     *
     * Proxy to {@link Zend_Filter::setDefaultNamespaces()}
     *
     * @param array|string $namespace
     * @return void
     */
    public static function setDefaultNamespaces($namespace)
    {
        Zend_Filter::setDefaultNamespaces($namespace);
    }

    /**
     * Adds a new default namespace
     *
     * Proxy to {@link Zend_Filter::addDefaultNamespaces()}
     *
     * @param array|string $namespace
     * @return void
     */
    public static function addDefaultNamespaces($namespace)
    {
        Zend_Filter::addDefaultNamespaces($namespace);
    }

    /**
     * Returns true when defaultNamespaces are set
     *
     * Proxy to {@link Zend_Filter::hasDefaultNamespaces()}
     *
     * @return boolean
     */
    public static function hasDefaultNamespaces()
    {
        return Zend_Filter::hasDefaultNamespaces();
    }

    /**
     * Filter value through a specified filter class, without requiring separate
     * instantiation of the filter object.
     *
     * Proxy to {@link Zend_Filter::filterStatic()}
     *
     * @param  mixed $value
     * @param  string $classBaseName
     * @param  array $args OPTIONAL
     * @param  array|string $namespaces OPTIONAL
     * @return mixed
     * @throws Zend_Filter_Exception
     */
    public static function filterStatic($value, $classBaseName, array $args = array(), $namespaces = array())
    {
        return Zend_Filter::filterStatic($value, $classBaseName, $args, $namespaces);
    }
}
