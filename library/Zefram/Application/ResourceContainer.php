<?php

/**
 * Resource container with lazy object initialization.
 *
 * @version 2015-12-22 / 2015-03-30
 */
class Zefram_Application_ResourceContainer implements ArrayAccess
{
    /**
     * Initialized resources
     * @var array
     */
    protected $_resources = array();

    /**
     * Resource aliases
     * @var array
     */
    protected $_aliases = array();

    /**
     * Resource definitions
     * @var array
     */
    protected $_definitions = array();

    /**
     * Resource callbacks
     * @var array
     */
    protected $_callbacks = array();

    /**
     * @param array|object $options
     */
    public function __construct($options = null) // {{{
    {
        if (is_object($options) && method_exists($options, 'toArray')) {
            $options = (array) $options->toArray();
        }

        if (is_array($options)) {
            $this->addResources($options);
        }
    } // }}}

    /**
     * Add many resources at once
     *
     * @param  array|Traversable $resources
     * @return Zefram_Application_ResourceContainer
     */
    public function addResources($resources) // {{{
    {
        foreach ($resources as $name => $resource) {
            $this->addResource($name, $resource);
        }
        return $this;
    } // }}}

    /**
     * Add a resource
     *
     * @param  string $name
     * @param  string|array|object $resource
     * @return Zefram_Application_ResourceContainer
     * @throws Zend_Application_Exception
     */
    public function addResource($name, $resource) // {{{
    {
        $name = $this->_foldCase($name);

        if (isset($this->_resources[$name])) {
            throw new Zend_Application_Exception("Resource '$name' is already registered");
        }

        if (is_string($resource)) {
            if (!strncasecmp($resource, 'resource:', 9)) {
                $this->_aliases[$name] = substr($resource, 9);
                return $this;
            }

            // string not begining with 'resource:' is considered to be
            // a class name only definition - but this feature is deprecated
            if (class_exists($resource)) {
                trigger_error(sprintf('Using string values as resource class names is deprecated (%s)', $resource), E_USER_WARNING);
                $resource = array('class' => $resource);
            }
        }

        if (is_array($resource) && isset($resource['class'])) {
            $this->_definitions[$name] = $resource;
        } elseif (is_array($resource) && isset($resource['callback'])) {
            $args = isset($resource['args']) ? array_values($resource['args']) : array();
            $this->addResourceCallback($name, $resource['callback'], $args);
        } else {
            $this->_resources[$name] = $resource;
        }

        return $this;
    } // }}}

    /**
     * @param string $name
     * @param callable|Zend_Stdlib_CallbackHandler $callback
     * @param array $args OPTIONAL
     * @return $this
     */
    public function addResourceCallback($name, $callback, array $args = array())
    {
        if (!$callback instanceof Zefram_Stdlib_CallbackHandler) {
            $callback = new Zefram_Stdlib_CallbackHandler($callback, $args);
        }

        $name = $this->_foldCase($name);
        $this->_callbacks[$name] = $callback;

        return $this;
    }

    /**
     * Retrieve a resource instance
     *
     * @param  string $name
     * @return mixed
     * @throws Zend_Application_Exception
     */
    public function getResource($name) // {{{
    {
        $name = $this->_foldCase($name);

        // resource cannot be null, so check with isset() is sufficient
        if (isset($this->_resources[$name])) {
            return $this->_resources[$name];
        }

        // when resolving new resources, check for definitions first,
        // then aliases.

        $resource = null;

        // After a resource is instantiated from definition, the definition
        // is removed
        if (isset($this->_definitions[$name])) {
            $resource = $this->_createInstance($this->_definitions[$name]);
            unset($this->_definitions[$name]);
        }

        if (isset($this->_aliases[$name])) {
            if (!$this->hasResource($name)) {
                throw new Zend_Application_Exception("Invalid resource alias '$name'");
            }
            $resource = $this->getResource($this->_aliases[$name]);
            unset($this->_aliases[$name]);
        }

        if (isset($this->_callbacks[$name])) {
            $resource = $this->_callbacks[$name]->__invoke($this);
            if ($resource === null) {
                throw new Zend_Application_Exception("Could not create instance of '$name' from callback");
            }
            unset($this->_callbacks[$name]);
        }

        if ($resource === null) {
            throw new Zend_Application_Exception("No resource is registered for key '$name'");
        }

        $this->_resources[$name] = $resource;
        return $resource;
    } // }}}

    /**
     * Remove resource from container.
     *
     * @param  string $name
     * @return Zefram_Application_ResourceContainer
     */
    public function removeResource($name) // {{{
    {
        $name = $this->_foldCase($name);
        unset(
            $this->_resources[$name],
            $this->_definitions[$name],
            $this->_aliases[$name],
            $this->_callbacks[$name]
        );
    } // }}}

    /**
     * Is given resource registered in the container?
     *
     * @param  string $name
     * @return bool
     */
    public function hasResource($name) // {{{
    {
        $name = $this->_foldCase($name);
        return isset($this->_resources[$name])
            || isset($this->_definitions[$name])
            || isset($this->_aliases[$name])
            || isset($this->_callbacks[$name]);
    } // }}}

    /**
     * Helper method to maintain case-insensitivity of resource names.
     *
     * @param  string $key
     * @return string
     */
    protected function _foldCase($key) // {{{
    {
        return strtolower($key);
    } // }}}

    /**
     * @param  array|object $params
     * @return array
     */
    protected function _prepareParams($params) // {{{
    {
        if (is_object($params) && method_exists($params, 'toArray')) {
            $params = $params->toArray();
        }

        $params = (array) $params;

        foreach ($params as $key => $value) {
            if (is_string($value) && !strncasecmp($value, 'resource:', 9)) {
                $params[$key] = $this->getResource(substr($value, 9));
            }
            // recursively replace arrays with 'class' key with instances of
            // matching classes
            if (is_array($value)) {
                if (isset($value['class'])) {
                    $params[$key] = $this->_createInstance($value);
                } else {
                    $params[$key] = $this->_prepareParams($value);
                }
            }
        }

        return $params;
    } // }}}

    /**
     * Create an instance of a given class and setup its parameters.
     *
     * @param  array $description
     * @return object
     * @throws Zend_Application_Exception
     */
    protected function _createInstance(array $description) // {{{
    {
        if (empty($description['class'])) {
            throw new Zend_Application_Exception('No class name found in description');
        }

        $class = $description['class'];
        $params = null;

        if (isset($description['params'])) {
            $params = $this->_prepareParams($description['params']);
        }

        // instantiate object, pass 'args' to constructor
        $args = null;
        if (isset($description['args'])) {
            $args = $this->_prepareParams($description['args']);
        }

        if ($args) {
            $ref = new ReflectionClass($class);
            if ($ref->hasMethod('__construct')) {
                $instance = $ref->newInstanceArgs($args);
            } else {
                $instance = $ref->newInstance();
            }
        } else {
            $instance = new $class();
        }

        // this is now deprecated. Params will be passed to constructor
        foreach ((array) $params as $key => $value) {
            $methods = array(
                'set' . str_replace('_', '', $key),
                'set' . $key
            );
            foreach ($methods as $method) {
                if (method_exists($instance, $method)) {
                    $instance->{$method}($value);
                    break;
                }
            }
        }

        // Set options using setter methods, try camel-cased versions
        // first, then underscored. Because PHP is case-insensitive when
        // it comes to function names, there is no need to appy some fancy
        // underscore-to-camel-case filter. Removing all underscore is
        // sufficient.
        if (isset($description['options'])) {
            $options = $this->_prepareParams($description['options']);

            foreach ($options as $key => $value) {
                $methods = array(
                    'set' . str_replace('_', '', $key),
                    'set' . $key
                );
                foreach ($methods as $method) {
                    if (method_exists($instance, $method)) {
                        $instance->{$method}($value);
                        break;
                    }
                }
            }
        }

        // invoke arbitrary methods
        if (isset($description['invoke'])) {
            foreach ($description['invoke'] as $invoke) {
                if (!is_array($invoke)) {
                    throw new Zend_Application_Exception('Invoke value must be an array');
                }
                $method = array_shift($invoke);
                $args = (array) array_shift($invoke);
                call_user_func_array(array($instance, $method), $args);
            }
        }

        return $instance;
    } // }}}

    /**
     * Proxy to {@see getResource()}.
     *
     * This function is expected to be called by Bootstrap.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getResource($name);
    }

    /**
     * Proxy to {@see addResource()}.
     *
     * This function is expected to be called by Bootstrap.
     *
     * @param  string $name
     * @param  mixed $resource
     * @return void
     */
    public function __set($name, $resource)
    {
        $this->addResource($name, $resource);
    }

    /**
     * Proxy to {@link hasResource()}.
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->hasResource($name);
    }

    /**
     * Proxy to {@link removeResource()}.
     *
     * @param string $name
     * @return void
     */
    public function __unset($name)
    {
        $this->removeResource($name);
    }

    /**
     * Required by ArrayAccess interface.
     *
     * Proxy to {@link getResource()}.
     *
     * @param  string $name
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->getResource($name);
    }

    /**
     * Required by ArrayAccess interface.
     *
     * Proxy to {@link setResource()}.
     *
     * @param  string $name
     * @param  mixed $resource
     * @return void
     */
    public function offsetSet($name, $resource)
    {
        $this->addResource($name, $resource);
    }

    /**
     * Required by ArrayAccess interface.
     *
     * Proxy to {@link hasResource()}.
     *
     * @param  string $name
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->hasResource($name);
    }

    /**
     * Required by ArrayAccess interface.
     *
     * Proxy to {@link removeResource()}.
     *
     * @param  string $name
     * @return void
     */
    public function offsetUnset($name)
    {
        $this->removeResource($name);
    }
}
