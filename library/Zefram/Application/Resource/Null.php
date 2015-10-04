<?php

/**
 * @category   Zefram
 * @package    Zefram_Application
 * @subpackage Resource
 * @author     xemlock <xemlock@gmail.com>
 */
class Zefram_Application_Resource_Null extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zefram_Application_Resource_Null
     */
    protected static $_instance;

    /**
     * Do nothing
     *
     * @return void
     */
    public function init()
    {}

    /**
     * Retrieve null resource instance
     *
     * @return Zefram_Application_Resource_Null
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}