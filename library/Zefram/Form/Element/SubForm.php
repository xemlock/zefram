<?php

/**
 * Subforms as a solution to composite elements was not the luckiest idea.
 * The aim of this class is to make subforms handling the same as ordinary
 * form elements.
 *
 * @category Zefram
 * @package  Zefram_Form
 * @version  2017-02-15
 */
class Zefram_Form_Element_SubForm extends Zend_Form_Element
{
    public $helper = 'form';

    /**
     * @var bool
     */
    protected $_isArray = true;

    /**
     * @var Zend_Form
     */
    protected $_form;

    /**
     * @param Zend_Form $form
     * @return Zefram_Form_Element_SubForm
     */
    public function setForm(Zend_Form $form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * @return Zend_Form
     * @throws Zend_Form_Element_Exception
     */
    public function getForm()
    {
        if (!$this->_form) {
            throw new Zend_Form_Element_Exception('Form is not set');
        }
        return $this->_form;
    }

    /**
     * @return array
     */
    public function getValue()
    {
        return $this->getForm()->getValues();
    }

    /**
     * @return array
     */
    public function getUnfilteredValue()
    {
        return $this->getForm()->getUnfilteredValues();
    }

    /**
     * @param  array $value
     * @return Zefram_Form_Element_HiddenArray
     */
    public function setValue($value)
    {
        $this->getForm()->setDefaults((array) $value);
        return $this;
    }

    /**
     * @param  mixed $value
     * @param  array $context OPTIONAL
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        return $this->getForm()->isValid($context);
    }

    public function setTranslator($translator = null)
    {
        $this->getForm()->setTranslator($translator);
        return $this;
    }

    public function getTranslator()
    {
        return $this->getForm()->getTranslator();
    }

    public function getMessages()
    {
        return $this->getForm()->getMessages();
    }

    public function getErrorMessages()
    {
        return $this->getForm()->getErrorMessages();
    }

    public function getErrors()
    {
        return $this->getForm()->getErrors();
    }

    public function clearErrorMessages()
    {
        return $this->getForm()->clearErrorMessages();
    }

    public function __get($key)
    {
        return $this->getForm()->{$key};
    }

    public function __set($key, $value)
    {
        if (is_scalar($value)) {
            $this->getForm()->setAttrib($key, $value);
        } else {
            $this->getForm()->{$key} = $value;
        }
    }

    public function __isset($key)
    {
        return isset($this->getForm()->{$key});
    }
}
