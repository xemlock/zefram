<?php

/**
 * This class works as a wrapper for resource definition understood by
 * {@link Zefram_Application_ResourceContainer}.
 * Actually it can work as a plugin wrapper for any value!!!
 *
 * @category   Zefram
 * @package    Zefram_Application
 * @subpackage Bootstrap
 * @author     xemlock
 */
class Zefram_Application_Resource_ResourceDefinition
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var object
     */
    protected $_container;

    /**
     * @var string
     */
    protected $_containerKey;

    /**
     * @var mixed
     */
    protected $_data;

    /**
     * @param object $container
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setContainer($container)
    {
        if (!is_object($container)) {
            throw new InvalidArgumentException('Resource container must be an object');
        }
        $this->_container = $container;
        return $this;
    }

    /**
     * @param string $containerKey
     * @return $this
     */
    public function setContainerKey($containerKey)
    {
        $this->_containerKey = (string) $containerKey;
        return $this;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->_data = $data;
        $this->_addToContainer();
        return $this;
    }

    /**
     * @param array $options
     * @return Zend_Application_Resource_ResourceAbstract
     */
    public function setOptions(array $options)
    {
        // setup container and container key before setting data

        if (isset($options['container'])) {
            $this->setContainer($options['container']);
            unset($options['container']);
        }

        if (isset($options['containerKey'])) {
            $this->setContainerKey($options['containerKey']);
            unset($options['containerKey']);
        }

        return parent::setOptions($options);
    }

    /**
     * @return void
     */
    public function init()
    {
        // do nothing. Resource is automatically bootstrapped whenever
        // options are changed. Do not return anything, so that bootstrap
        // does not overwrite resource in container
    }

    /**
     * @return $this
     */
    public function unregister()
    {
        $this->_removeFromContainer();
        return $this;
    }

    protected function _addToContainer()
    {
        if ($this->_container) {
            unset($this->_container->{$this->_containerKey});
            $this->_container->{$this->_containerKey} = $this->_data;
        }
    }

    protected function _removeFromContainer()
    {
        if ($this->_container) {
            unset($this->_container->{$this->_containerKey});
        }
    }
}
