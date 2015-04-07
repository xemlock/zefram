<?php

class Zefram_View_Helper_FlashMessenger extends Zend_View_Helper_Abstract
{
    /**
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $_flashMessenger;

    /**
     * Proxy method calls to flash messenger action helper
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->getFlashMessenger(), $method), $args);
    }

    /**
     * Returns the flash messenger action helper
     *
     * @return Zend_Controller_Action_Helper_FlashMessenger
     */
    public function getFlashMessenger()
    {
        if (null === $this->_flashMessenger) {
            $this->_flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('flashMessenger');
        }
        return $this->_flashMessenger;
    }

    public function getMessage($namespace = null)
    {
        foreach ($this->getMessages($namespace) as $message) {
            return $message;
        }
    }

    public function __toString()
    {
        return (string) $this->getMessage();
    }

    public function flashMessenger()
    {
        return $this;
    }
}
