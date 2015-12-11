<?php

class Zefram_View_Helper_FormElement extends Zend_View_Helper_FormElement
{
    public function formElement($type, $name, array $attribs = null, array $options = null, $listsep = null)
    {
        $value = null;

        if ($name instanceof Zend_Form_Element) {
            $element = $name;
            $name = $element->getFullyQualifiedName();

            if ($element instanceof Zend_Form_Element_Button) {
                $value = $element->getLabel();
            } else {
                $value = $element->getValue();
            }

            $attribs = array_merge($element->getAttribs(), is_array($attribs) ? $attribs : array());
            $options = array_merge($element->options, is_array($options) ? $options : array());
        }

        if (isset($attribs['value'])) {
            $value = $attribs['value'];
        }

        $view = $this->view;
        if (null === $view) {
            throw new Zend_View_Exception('FormElement view helper cannot render without a registered view object');
        }

        $helper = 'form' . ucfirst($type);
        return $view->$helper($name, $value, $attribs, $options, $listsep);
    }
}
