<?php

/**
 * @method Zefram_View_Abstract getView()
 */
class Zefram_Form_Element_MultiCheckbox extends Zend_Form_Element_MultiCheckbox
{
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        Zefram_Form_Element::_loadDefaultDecorators($this);

        // Disable 'for' attribute
        if (false !== $decorator = $this->getDecorator('label')) {
            $decorator->setOption('disableFor', true);
        }

        return $this;
    }
}
