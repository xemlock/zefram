<?php

/**
 * @method Zefram_View_Abstract getView()
 */
class Zefram_Form_Element_File extends Zend_Form_Element_File
{
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        Zefram_Form_Element::_loadDefaultDecorators($this);

        // This element needs the File decorator and not the ViewHelper decorator
        if (false !== $this->getDecorator('ViewHelper')) {
            $this->removeDecorator('ViewHelper');
        }
        if (false === $this->getDecorator('File')) {
            // Add File decorator to the beginning
            $decorators = $this->getDecorators();
            array_unshift($decorators, 'File');
            $this->setDecorators($decorators);
        }

        return $this;
    }
}
