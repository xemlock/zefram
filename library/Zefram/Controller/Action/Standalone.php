<?php

/**
 * Class for encapsulation of a standalone action logic.
 *
 * @version 2013-06-30 / 2013-05-02
 * @method Zend_Controller_Front getFrontController()
 * @method Zend_Controller_Request_Abstract getRequest()
 * @method Zend_Controller_Response_Abstract getResponse()
 *
 * @method Zend_Controller_Action setParam()
 * @method bool hasParam(string $name) Proxy to Zend_Controller_Action::hasParam()
 * @method mixed getParam(string $name, $default = null) Proxy to Zend_Controller_Action::getParam()
 * @method array getAllParams() Proxy to Zend_Controller_Action::getAllParams()
 * @method void forward(string $action, string $controller = null, string $module = null, array $params = null) Proxy to Zend_Controller_Action::forward()
 * @method void redirect(string $url, array $options = array()) Proxy to Zend_Controller_Action::redirect()
 *
 * @method mixed getScalarParam(string $name, $default = null) Proxy to Zefram_Controller_Action::getScalarParam()
 * @method mixed getResource(string $name, bool $throw = true) Proxy to Zefram_Controller_Action::getResource()
 */
abstract class Zefram_Controller_Action_Standalone
{
    /**
     * @var string
     */
    protected $_actionControllerClass = 'Zefram_Controller_Action';

    /**
     * @var Zend_Controller_Action
     */
    protected $_actionController;

    /**
     * @var Zefram_Controller_Action_Standalone_HelperBroker
     */
    protected $_helper;

    /**
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * @var Zend_Controller_Response_Abstract
     */
    protected $_response;

    /**
     * @var Zend_View_Interface
     */
    public $view;

    /**
     * @param  Zend_Controller_Action $actionController
     * @throws Zefram_Controller_Action_Exception_InvalidArgument
     */
    public function __construct(Zend_Controller_Action $actionController) 
    {
        if (null !== $this->_actionControllerClass && !$actionController instanceof $this->_actionControllerClass) {
            throw new Zefram_Controller_Action_Exception_InvalidArgument(sprintf(
                "The specified controller is of class %s, expecting class to be an instance of %s",
                get_class($actionController),
                $this->_actionControllerClass
            ));
        }

        $this->_actionController = $actionController;

        $this->_request = $actionController->getRequest();
        $this->_response = $actionController->getResponse();

        $this->_helper = new Zefram_Controller_Action_Standalone_HelperBroker($this);
        $this->view = $actionController->view;

        $this->_init();
    }

    protected function _init()
    {}

    /**
     * @return Zend_Controller_Action
     */
    public function getActionController()
    {
        return $this->_actionController;
    }

    /**
     * Proxies to {@link getActionController()}.
     * @deprecated Use getActionController() instead
     */
    public function getController()
    {
        return $this->getActionController();
    }

    /**
     * @return Zend_View_Interface
     */
    public function getView()
    {
        return $this->_actionController->initView();
    }

    /**
     * Since this method is used by helper broker, for performance reasons
     * it is declared here and not discovered using __call magic method.
     *
     * @param  $name
     * @return Zend_Controller_Action_Helper_Abstract
     */
    public function getHelper($name)
    {
        return $this->_actionController->getHelper($name);
    }

    abstract public function run();

    /**
     * Call action controller method.
     *
     * @param  string $name
     * @param  array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // is_callable returns true if __call is present.
        $callback = array($this->_actionController, $name);
        return call_user_func_array($callback, $arguments);
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed|null
     * @deprecated
     */
    protected function _getParam($name, $default = null)
    {
        $value = $this->_request->getParam($name, $default);
        if (null === $value || '' === $value) {
            $value = $default;
        }
        return $value;
    }

    /**
     * @param $url
     * @param array $options
     * @deprecated
     */
    protected function _redirect($url, array $options = array())
    {
        $this->_helper->redirector->gotoUrl($url, $options);
    }

    /**
     * @param $message
     * @param null $namespace
     * @deprecated
     */
    protected function _flashMessage($message, $namespace = null)
    {
        $this->_helper->flashMessenger->addMessage($message, $namespace);
    }
}
