<?php

/**
 * @method Zefram_View_Abstract getView()
 */
class Zefram_Form_Element_Submit extends Zend_Form_Element_Submit
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
