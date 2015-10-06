<?php

/**
 * @category   Zefram
 * @package    Zefram_Application
 * @subpackage Bootstrap
 * @author     xemlock
 */
class Zefram_Application_Resource_DataWrapper
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Explicit name that a this resource will register as
     * @var string
     */
    public $_explicitType;

    /**
     * @var mixed
     */
    protected $_data;

    /**
     * Set resource data
     *
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Set explicit name that a this resource will register as
     *
     * @param string $explicitType
     * @return $this
     */
    public function setExplicitType($explicitType)
    {
        $this->_explicitType = $explicitType;
        return $this;
    }

    /**
     * Retrieve resource data
     *
     * @return mixed
     */
    public function init()
    {
        return $this->_data;
    }
}
