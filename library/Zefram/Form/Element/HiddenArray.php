<?php

/**
 * @version 2014-04-30
 */
class Zefram_Form_Element_HiddenArray extends Zefram_Form_Element_Array
{
    /**
     * Default view helper to use
     * @var string
     */
    public $helper = 'formHiddenArray';

    /**
     * Element decorators
     * @var array
     */
    protected $_decorators = array('ViewHelper');
}
