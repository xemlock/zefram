<?php

/**
 * @method Zefram_View_Abstract getView()
 */
class Zefram_Form_Element_Radio extends Zend_Form_Element_Radio
{
    public function loadDefaultDecorators()
    {
        Zefram_Form_Element::_loadDefaultDecorators($this);

        // Disable 'for' attribute
        if (isset($this->_decorators['Label']) &&
            !isset($this->_decorators['Label']['options']['disableFor'])
        ) {
            $this->_decorators['Label']['options']['disableFor'] = true;
        }

        return $this;
    }
}
