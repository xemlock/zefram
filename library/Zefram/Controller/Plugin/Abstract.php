<?php

abstract class Zefram_Controller_Plugin_Abstract extends Zend_Controller_Plugin_Abstract
{
    /**
     * Retrieve a resource from bootstrap
     *
     * @param  string $resource
     * @param  bool $throw OPTIONAL
     * @return mixed
     */
    public function getResource($resource, $throw = true)
    {
        /** @var $helper Zefram_Controller_Action_Helper_Resource */
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('Resource');
        return $helper->getResource($resource, $throw);
    }
}
