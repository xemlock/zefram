<?php

/**
 * A enhanced replacement for {@link Zend_Controller_Action_Helper_ViewRenderer}
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
}
