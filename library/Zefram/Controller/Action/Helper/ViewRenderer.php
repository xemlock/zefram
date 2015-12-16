<?php

/**
 * A enhanced replacement for {@link Zend_Controller_Action_Helper_ViewRenderer}
 *
 * This helper allows to specify path specifications for each module separately.
 *
 * @author xemlock <xemlock@gmail.com>
 */
class Zefram_Controller_Action_Helper_ViewRenderer
    extends Zend_Controller_Action_Helper_ViewRenderer
{
    /**
     * @var array
     */
    protected $_moduleViewScriptPathSpec;

    /**
     * @var array
     */
    protected $_moduleViewScriptPathNoControllerSpec;

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
        return $this->_moduleViewScriptPathNoControllerSpec;
    }

    public function setViewScriptPathNoControllerSpec($path, $module = null)
    {
        if (null === $module) {
            $this->_moduleViewScriptPathNoControllerSpec = (string) $path;
        } else {
            $this->_moduleViewScriptPathNoControllerSpec[$module] = (string) $path;
        }
        return $this;
    }
}
