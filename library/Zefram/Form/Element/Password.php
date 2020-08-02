<?php

/**
 * @method Zefram_View_Abstract getView()
 */
class Zefram_Form_Element_Password extends Zend_Form_Element_Password
{
    public function loadDefaultDecorators()
    {
        Zefram_Form_Element::_loadDefaultDecorators($this);
        return $this;
    }
}
