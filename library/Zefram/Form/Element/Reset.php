<?php

/**
 * @method Zefram_View_Abstract getView()
 */
class Zefram_Form_Element_Reset extends Zend_Form_Element_Reset
{
    public function loadDefaultDecorators()
    {
        Zefram_Form_Element::_loadDefaultDecorators($this);
        return $this;
    }
}
