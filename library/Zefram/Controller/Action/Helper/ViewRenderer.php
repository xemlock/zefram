<?php

/**
 * A replacement for {@link Zend_Controller_Action_Helper_ViewRenderer}.
 *
 * This helper allows to specify path specifications and script suffixes for each
 * module separately. This allows detection of view engine to be used when rendering
 * view scripts from that module.
 *
 * It also provides a fixed implementation of getModule(), which does not broke if
 * no request is present.
 *
 * @author xemlock <xemlock@gmail.com>
 */
class Zefram_Controller_Action_Helper_ViewRenderer
    extends Zend_Controller_Action_Helper_ViewRenderer
{
    /**
     * @var array
     */
    protected $_moduleViewBasePathSpec;

    /**
     * @var array
     */
    protected $_moduleViewScriptPathSpec;

    /**
     * @var array
     */
    protected $_moduleViewScriptPathNoControllerSpec;

    /**
     * @var array
     */
    protected $_moduleViewSuffix;

    /**
     * Get current module name
     *
     * First module name is retrieved from request, if it is not available
     * (e.g. during bootstrapping or request's module name is empty)
     * the dispatcher's default module is used.
     *
     * @return string
     */
    public function getModule()
    {
        $request = $this->getRequest();
        $module  = $request ? $request->getModuleName() : null;
        if (null === $module) {
            $module = $this->getFrontController()->getDispatcher()->getDefaultModule();
        }
        return $module;
    }

    public function getViewBasePathSpec($module = null)
    {
        if (null === $module) {
            $module = $this->getModule();
        }
        if (isset($this->_moduleViewBasePathSpec[$module])) {
            return $this->_moduleViewBasePathSpec[$module];
        }
        return $this->_viewBasePathSpec;
    }

    public function setViewBasePathSpec($path, $module = null)
    {
        if (null === $module) {
            $this->_viewBasePathSpec = (string) $path;
        } else {
            $this->_moduleViewBasePathSpec[$module] = (string) $path;
        }
        return $this;
    }

    public function getViewScriptPathSpec($module = null)
    {
        if (null === $module) {
            $module = $this->getModule();
        }
        if (isset($this->_moduleViewScriptPathSpec[$module])) {
            return $this->_moduleViewScriptPathSpec[$module];
        }
        return $this->_viewScriptPathSpec;
    }

    public function setViewScriptPathSpec($path, $module = null)
    {
        if (null === $module) {
            $this->_viewScriptPathSpec = (string) $path;
        } else {
            $this->_moduleViewScriptPathSpec[$module] = (string) $path;
        }
        return $this;
    }

    public function getViewScriptPathNoControllerSpec($module = null)
    {
        if (null === $module) {
            $module = $this->getModule();
        }
        if (isset($this->_moduleViewScriptPathNoControllerSpec[$module])) {
            return $this->_moduleViewScriptPathNoControllerSpec[$module];
        }
        return $this->_viewScriptPathNoControllerSpec;
    }

    public function setViewScriptPathNoControllerSpec($path, $module = null)
    {
        if (null === $module) {
            $this->_viewScriptPathNoControllerSpec = (string) $path;
        } else {
            $this->_moduleViewScriptPathNoControllerSpec[$module] = (string) $path;
        }
        return $this;
    }

    public function getViewSuffix($module = null)
    {
        if (null === $module) {
            $module = $this->getModule();
        }
        if (isset($this->_moduleViewSuffix[$module])) {
            return $this->_moduleViewSuffix[$module];
        }
        return $this->_viewSuffix;
    }

    public function setViewSuffix($suffix, $module = null)
    {
        if (null === $module) {
            $this->_viewSuffix = (string) $suffix;
        } else {
            $this->_moduleViewSuffix[$module] = (string) $suffix;
        }
        return $this;
    }

    protected function _translateSpec(array $vars = array())
    {
        if (empty($vars['suffix'])) {
            $vars['suffix'] = $this->getViewSuffix();
        }
        return parent::_translateSpec($vars);
    }
}
