<?php

/**
 * @author xemlock
 * @version 2013-11-10
 */
abstract class Zefram_Controller_Action_Helper_Abstract extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var string
     */
    protected $_actionControllerClass;

    /**
     * Set action controller.
     *
     * @param  Zend_Controller_Action $actionController
     * @return Zefram_Controller_Action_Helper_Abstract
     * @throws Zefram_Controller_Action_Exception_InvalidArgument
     */
    public function setActionController(Zend_Controller_Action $actionController = null)
    {
        if (null !== $actionController && 
            null !== $this->_actionControllerClass &&
            !$actionController instanceof $this->_actionControllerClass
        ) {
            throw new Zefram_Controller_Action_Exception_InvalidArgument(sprintf(
                "The specified controller is of class %s, expecting class to be an instance of %s",
                get_class($actionController),
                $this->_actionControllerClass
            ));
        }
        return parent::setActionController($actionController);
    }

    /**
     * Get view object from action controller.
     *
     * @return Zend_View_Interface
     */
    public function getView()
    {
        return $this->getActionController()->view;
    }

    /**
     * Get action helper from action controller.
     *
     * @param  string $helperName
     * @return Zend_Controller_Action_Helper_Abstract
     */
    public function getHelper($helperName)
    {
        return $this->getActionController()->getHelper($helperName);
    }

    /**
     * Call method on action controller.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(
            array($this->getActionController(), $method),
            $args
        );
    }
}
