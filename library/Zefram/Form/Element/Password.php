<?php

/**
 * @method Zefram_View_Abstract getView()
 */
class Zefram_Form_Element_Password extends Zend_Form_Element_Password
{
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        Zefram_Form_Element::_loadDefaultDecorators($this);
        return $this;
    }
}
