<?php

/**
 * @property Zefram_Controller_Action_HelperBroker $_helper
 * @property Zefram_View_Interface $view
 * @version 2014-09-25
 */
class Zefram_Controller_Action extends Zend_Controller_Action
{
    /**
     * Stores the name of the currently dispatched action
     * @var string
     */
    protected $_dispatchAction;

    /**
     * @return string|null
     */
    public function getDispatchAction()
    {
        return $this->_dispatchAction;
    }

    /**
     * {@inheritDoc}
     *
     * Original dispatch() implementation offers no ability do get the currently
     * dispatched action in preDispatch() and postDispatch() routines. Here, the
     * currently dispatched action name is stored in $_dispatchAction property.
     *
     * This functionality is needed, as the dispatched action name cannot be
     * changed by action helpers, whereas the value of 'action' request
     * parameter can.
     */
    public function dispatch($action)
    {
        $this->_dispatchAction = (string) $action;
        try {
            parent::dispatch($action);
            $this->_dispatchAction = null;
        } catch (Exception $e) {
            $this->_dispatchAction = null;
            throw $e;
        }
    }

    /**
     * @param string|ReflectionClass $controllerClass
     * @param string $actionMethod
     * @return string|false
     */
    public static function loadActionClass($controllerClass, $actionMethod)
    {
        $actionMethod = ucfirst($actionMethod);

        if ($controllerClass instanceof ReflectionClass) {
            $controllerRef = $controllerClass;
            $controllerClass = $controllerRef->getName();
        } else {
            $controllerRef = null;
        }

        $actionClass = $controllerClass . '_' . $actionMethod;

        if (!class_exists($actionClass, false)) {
            // file containing action implementation must reside in the
            // directory having the same name as controller class
            if (null === $controllerRef) {
                $controllerRef = new ReflectionClass($controllerClass);
            }

            $actionDir = $controllerRef->getFileName();

            // strip extension(s) from controller file name to get path
            // to action directory
            if (false !== ($pos = strrpos($actionDir, '.'))) {
                $actionDir = substr($actionDir, 0, $pos);
            }

            $actionFile = $actionDir . '/' . $actionMethod . '.php';

            if (is_file($actionFile) && is_readable($actionFile)) {
                include_once $actionFile;
                if (class_exists($actionClass, false)) {
                    return $actionClass;
                }
            }

            return false;
        }

        return $actionClass;
    }

    public function getActionClass($actionName)
    {
        $controllerClass = get_class($this);
        $actionMethod = ucfirst(preg_replace_callback(
            '/-([a-zA-Z0-9]+)/',
            create_function('$match', 'return ucfirst($match[1]);'),
            $actionName
        ));
        $actionMethod .= 'Action';

        return self::loadActionClass($controllerClass, $actionMethod);
    }

    public function __call($method, $arguments)
    {
        if (!strcasecmp(substr($method, -6), 'Action')) {
            // undefined action, try running standalone action
            $actionClass = self::loadActionClass(get_class($this), $method);
            if ($actionClass) {
                $ref = new ReflectionClass($actionClass);
                if ($ref->hasMethod('__construct')) {
                    array_unshift($arguments, $this);
                    $actionObj = $ref->newInstanceArgs($arguments);
                } else {
                    $actionObj = $ref->newInstance($this);
                }
                return $actionObj->run();
            }
        }
        // fallback to default handling of undefined methods
        parent::__call($method, $arguments);
    }

    /**
     * null is not considered a scalar value
     * (@see http://php.net/manual/en/function.is-scalar.php)
     *
     * @param  string $name
     * @param  mixed $default
     * @param  scalar|null
     * @return mixed
     */
    public function getScalarParam($name, $default = null)
    {
        $value = parent::getParam($name, $default);
        return is_scalar($value) ? $value : $default;
    }

    /**
     * Get resource from container.
     *
     * @param  string $name
     * @param  bool $throw
     * @return mixed
     */
    public function getResource($name, $throw = true)
    {
        return $this->_helper->resource($name, $throw);
    }

    // all below methods are now deprecated

    protected $_ajaxResponseClass = 'Zefram_Controller_Action_AjaxResponse';

    protected $_ajaxResponse;

    /**
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     * @throws Exception
     * @deprecated Use {@link Zefram_Controller_Action_Helper_Resource} instead.
     */
    public function getBootstrap()
    {
        $bootstrap = $this->getFrontController()->getParam('bootstrap');

        if (!$bootstrap instanceof Zend_Application_Bootstrap_BootstrapAbstract) {
            throw new Exception('Bootstrap is not available');
        }

        return $bootstrap;
    }

    /**
     * Proxy to {@see getResource()}.
     *
     * @param  string $name
     * @return mixed
     * @deprecated Use {@link getResource()} instead.
     */
    public function getBootstrapResource($name)
    {
        return $this->getResource($name);
    }


    public function getAjaxResponse()
    {
        if (null === $this->_ajaxResponse) {
            $ajaxResponseClass = $this->_ajaxResponseClass;
            $this->setAjaxResponse(new $ajaxResponseClass);
        }
        return $this->_ajaxResponse;
    }

    public function setAjaxResponse(Zefram_Controller_Action_AjaxResponse_Abstract $ajaxResponse)
    {
        $this->_ajaxResponse = $ajaxResponse;
        return $this;
    }

    // additional proxies to helpers
    /**
     * @deprecated
     */
    protected function _flashMessage($message, $namespace = null)
    {
        return $this->flashMessage($message, $namespace);
    }

    /**
     * @param $message
     * @param null $namespace
     * @return $this
     * @deprecated
     */
    public function flashMessage($message, $namespace = null)
    {
        $this->_helper->flashMessenger->addMessage($message, $namespace);
        return $this;
    }

    /**
     * @throws Zend_Controller_Action_Exception
     * @deprecated
     */
    public function setLayout($layout)
    {
        $this->_helper->layout->setLayout($layout);
        return $this;
    }

    /**
     * @throws Zend_Controller_Action_Exception
     * @deprecated
     */
    public function disableLayout($disable = true)
    {
        $layout = $this->_helper->layout;
        if ($disable) {
            $layout->disableLayout();
        } else {
            $layout->enableLayout();
        }
        return $this;
    }

    /**
     * @param bool $disable
     * @return $this
     * @deprecated
     */
    public function disableView($disable = true)
    {
        $this->_helper->viewRenderer->setNoRender($disable);
        return $this;
    }
}
