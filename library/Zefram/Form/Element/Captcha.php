<?php

/**
 * @method Zefram_View_Abstract getView()
 */
class Zefram_Form_Element_Captcha extends Zend_Form_Element_Captcha
{
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        Zefram_Form_Element::_loadDefaultDecorators($this);

        if ($decorator = $this->getDecorator('HtmlTag')) {
            /** @var Zend_Form_Decorator_HtmlTag $decorator */
            $decorator->setOption('id', $this->getName() . '-element');
        }

        return $this;
    }
}
