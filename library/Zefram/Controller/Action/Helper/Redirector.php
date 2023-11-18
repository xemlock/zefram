<?php

/**
 * Replacement for {@link Zend_Controller_Action_Helper_Redirector} helper which
 * offers more sensible order of gotoRoute* arguments (route name first), and
 * better detection if baseUrl should be prepended in gotoUrl* calls.
 *
 * @method int getCode()             Retrieve HTTP status code to emit on redirection.
 * @method $this setCode(int $code)  Set HTTP status code for redirect behaviour.
 * @method bool getExit()            Retrieve flag for whether or not redirection will exit when finished.
 * @method $this setExit(bool $flag) Set exit flag for redirect behaviour.
 * @method bool getPrependBase()     Retrieve flag for whether redirect will prepend the base URL on relative URLs.
 * @method $this setPrependBase(bool $flag) Set 'prepend base' flag for redirect behaviour.
 * @method bool getCloseSessionOnExit() Retrieve flag for whether {@link redirectAndExit()} shall close the session before exiting.
 * @method $this setCloseSessionOnExit(bool $flag) Set flag for whether or not {@link redirectAndExit()} shall close the session before exiting.
 * @method bool getUseAbsoluteUri()  Return use absolute URI flag
 * @method $this setUseAbsoluteUri(bool $flag = true) Set use absolute URI flag
 * @method string getRedirectUrl()   Retrieve currently set URL for redirect
 * @method void setGotoSimple(string $action, string $controller = null, string $module = null, array $params = array())
 * @method void gotoSimple(string $action, string $controller = null, string $module = null, array $params = array())
 * @method void gotoSimpleAndExit(string $action, string $controller = null, string $module = null, array $params = array())
 * @method void goto()               Proxy to {@link gotoSimple()}
 * @method void gotoAndExit()        Proxy to {@link gotoSimpleAndExit()}
 * @method $this setGoto()           Proxy to {@link setGotoSimple()}
 * @method void redirectAndExit()    Perform exit for redirector
 * @method void direct(string $action, string $controller = null, string $module = null, array $params = array())
 */
class Zefram_Controller_Action_Helper_Redirector extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Controller_Action_Helper_Redirector
     */
    protected $_redirector;

    public function __construct()
    {
        $this->_redirector = new Zend_Controller_Action_Helper_Redirector();
    }

    public function __call($method, $args)
    {
        $result = call_user_func_array(array($this->_redirector, $method), $args);
        return ($result === $this->_redirector) ? $this : $result;
    }

    public function setActionController(Zend_Controller_Action $actionController = null)
    {
        parent::setActionController($actionController);
        $this->_redirector->setActionController($actionController);
        return $this;
    }

    /**
     * If $name parameter is an array, treat it as $urlOptions for
     * compatibility with original Redirector helper.
     *
     * @param string|array $name
     * @param string|array $urlOptions
     * @param bool $reset
     * @param bool $encode
     * @return void
     */
    public function gotoRoute($name, $urlOptions = null, $reset = false, $encode = true)
    {
        list($name, $urlOptions) = $this->_prepareGotoRoute($name, $urlOptions);
        $this->_redirector->gotoRoute((array) $urlOptions, $name, $reset, $encode);
    }

    /**
     * Redirect to a route-based URL, and immediately exit
     *
     * @param string|array $name
     * @param string|array $urlOptions
     * @param bool $reset
     * @return void
     */
    public function gotoRouteAndExit($name, $urlOptions = null, $reset = false)
    {
        list($name, $urlOptions) = $this->_prepareGotoRoute($name, $urlOptions);
        $this->_redirector->gotoRouteAndExit((array) $urlOptions, $name, $reset);
    }

    /**
     * Build a URL based on a route
     *
     * @param  string|array $name
     * @param  string|array $urlOptions
     * @param  boolean $reset
     * @param  boolean $encode
     * @return void
     */
    public function setGotoRoute($name, $urlOptions = null, $reset = false, $encode = true)
    {
        list($name, $urlOptions) = $this->_prepareGotoRoute($name, $urlOptions);
        $this->_redirector->setGotoRoute($urlOptions, $name, $reset, $encode);
    }

    protected function _prepareGotoRoute($name, $urlOptions)
    {
        if (is_array($name)) {
            list($name, $urlOptions) = array($urlOptions, $name);
        }
        return array($name, (array) $urlOptions);
    }

    /**
     * Perform a redirect to a URL
     *
     * @param  string $url
     * @param  array  $options
     * @return void
     */
    public function gotoUrl($url, array $options = array())
    {
        list($url, $options) = $this->_prepareGotoUrl($url, $options);
        return $this->_redirector->gotoUrl($url, $options);
    }

    /**
     * Set a URL string for a redirect, perform redirect, and immediately exit
     *
     * @param $url
     * @param array $options
     * @return void
     */
    public function gotoUrlAndExit($url, array $options = array())
    {
        list($url, $options) = $this->_prepareGotoUrl($url, $options);
        return $this->_redirector->gotoUrlAndExit($url, $options);
    }

    /**
     * Performs even smarter detection if baseUrl should be prepended.
     *
     * {@link Zend_Controller_Action_Helper_Redirector::setGotoUrl()}
     *
     * @param string $url
     * @param array  $options
     * @return void
     */
    public function setGotoUrl($url, array $options = null)
    {
        list($url, $options) = $this->_prepareGotoUrl($url, $options);
        $this->_redirector->setGotoUrl($url, $options);
    }

    protected function _prepareGotoUrl($url, $options)
    {
        $request = $this->getRequest();
        if ($request instanceof Zend_Controller_Request_Http) {
            if (!isset($options['prependBase']) && strlen($url)) {
                $base = $request->getBaseUrl();
                $options['prependBase'] = strncmp($url, $base, strlen($base));
            }
        }
        return array($url, (array) $options);
    }
}
