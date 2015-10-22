<?php

/**
 * Resource for initializing logger.
 *
 * Supported configuration options:
 *
 *   resources.log.factoryClass = "Zend_Log"
 * 
 * A single logger:
 *
 *   resources.log.writerName = <WRITER>
 *   resources.log.writerParams.<PARAM> = <VALUE>
 *   resources.log.filterName = <FILTER>
 *   resources.log.filterParams.<PARAM> = <VALUE>
 *
 * Multiple loggers:
 *
 *   resources.log.<FIRST_LOGGER>.writerName = <WRITER>
 *   resources.log.<FIRST_LOGGER>.writerParams.<PARAM> = <VALUE>
 *   resources.log.<FIRST_LOGGER>.filterName = <FILTER>
 *   resources.log.<FIRST_LOGGER>.filterParams.<PARAM> = <VALUE>
 *   resources.log.<SECOND_LOGGER>.writerName = <WRITER>
 *   resources.log.<SECOND_LOGGER>.writerParams.<PARAM> = <VALUE>
 *   resources.log.<SECOND_LOGGER>.filterName = <FILTER>
 *   resources.log.<SECOND_LOGGER>.filterParams.<PARAM> = <VALUE>
 *
 * Options supported by Zefram_Log::factory(), if set as a factoryClass:
 *
 *   resources.log.class = <CLASS>
 *   resources.log.errorMessageFormat = <FORMAT>
 *   resources.log.registerErrorHandler = 0
 */
class Zefram_Application_Resource_Log extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var string
     */
    protected $_factoryClass = 'Zend_Log';

    /**
     * @var Zend_Log
     */
    protected $_log;

    /**
     * {@inheritDoc}
     */
    public function setOptions(array $options)
    {
        if (array_key_exists('factoryClass', $options)) {
            $factoryClass = $options['factoryClass'];
            $refClass = new ReflectionClass($factoryClass);
            $factory = $refClass->getMethod('factory');

            if (!$factory || !$factory->isStatic()) {
                throw new Zend_Log_Exception('Log factory class must implement a static factory() method');
            }

            $this->_factoryClass = $factoryClass;
            unset($options['factoryClass']);
        }
        return parent::setOptions($options);
    }

    public function init()
    {
        return $this->getLog();
    }

    /**
     * Retrieve logger object
     *
     * @return Zend_Log
     */
    public function getLog()
    {
        if (null === $this->_log) {
            $factoryClass = $this->_factoryClass;
            $options = $this->getOptions();
            $this->_log = $factoryClass::factory($options);
        }
        return $this->_log;
    }
}
