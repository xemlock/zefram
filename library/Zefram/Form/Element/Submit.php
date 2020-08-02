<?php

/**
 * @method Zefram_View_Abstract getView()
 */
class Zefram_Form_Element_Submit extends Zend_Form_Element_Submit
{
    public function loadDefaultDecorators()
    {
        Zefram_Form_Element::_loadDefaultDecorators($this);
        return $this;
    }
}
