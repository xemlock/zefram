<?php

/**
 * This class works as a wrapper for resource definition understood by
 * {@link Zefram_Application_ResourceContainer}.
 * Actually it can work as a plugin wrapper for any value!!!
 *
 * @category   Zefram
 * @package    Zefram_Application
 * @subpackage Bootstrap
 * @version    2015-03-11
 * @author     xemlock
 */
class Zefram_Application_Resource_ResourceDefinition
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * Sets lazy resource configuration.
     *
     * @param  array $options
     * @return Zefram_Application_Resource_ResourceDefinition
     */
    public function setOptions(array $options)
    {
        $this->_options = array_merge(
            (array) $this->_options,
            $options
        );
        return $this;
    }

    /**
     * Returns a resource definition.
     *
     * @return array
     * @throws Zend_Application_Resource_Exception
     */
    public function init()
    {
        /* if (empty($this->_options['class'])) {
            throw new Zend_Application_Resource_Exception(
                'Resource definition requires a class name'
            );
        } */
        return $this->_options;
    }
}
