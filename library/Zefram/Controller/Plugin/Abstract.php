<?php

/**
 * @deprecated Instead of using getResource() pass dependencies explicitly
 */
abstract class Zefram_Controller_Plugin_Abstract extends Zend_Controller_Plugin_Abstract
{
    /**
     * Retrieve front controller instance
     *
     * @return Zend_Controller_Front
     */
    public function getFrontController()
    {
        return Zend_Controller_Front::getInstance();
    }

    /**
     * Retrieve a resource from bootstrap
     *
     * @param  string $resource
     * @param  bool $throw OPTIONAL
     * @return mixed
     * @deprecated Reference resources explicitly either in constructor or setters
     */
    public function getResource($resource, $throw = true)
    {
        /** @var $helper Zefram_Controller_Action_Helper_Resource */
        try {
            $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('Resource');
        } catch (Zend_Controller_Action_Exception $exception) {
            $helper = new Zefram_Controller_Action_Helper_Resource();
            Zend_Controller_Action_HelperBroker::addHelper($helper);
        }
        return $helper->getResource($resource, $throw);
    }
}
