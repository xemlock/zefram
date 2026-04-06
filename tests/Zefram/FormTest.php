<?php

class Zefram_FormTest extends PHPUnit_Framework_TestCase
{
    public function testCreateElement()
    {
        $form = new Zefram_Form();
        $element = $form->createElement('text', 'foo', array('required' => true));

        $this->assertTrue($element->isValid('foo'));

        $this->assertFalse($element->isValid(''));
        $this->assertNull($element->getValue());

        $this->assertTrue($element->isValid(' foo '));
        $this->assertSame('foo', $element->getValue());
    }

    public function testAddElementPrefixPaths()
    {
        $form = new Zefram_Form(array(
            'elementPrefixPath' => array(
                array(
                    'prefix' => 'Alpha_Beta_',
                    'path' => __DIR__,
                    'type' => Zend_Form_Element::FILTER,
                ),
            ),
        ));
        $form->addElementPrefixPaths(array(
            Zend_Form_Element::VALIDATE => array(
                'prefix' => 'Foo_Bar_',
                'path' => __DIR__,
            ),
        ));
        $form->addElement('text', 'element01', array(
            'filters' => array(
                'Filter100',
            ),
            'validators' => array(
                array('Validator200', true),
            ),
        ));
        $element = $form->getElement('element01');

        $this->assertInstanceOf('Alpha_Beta_Filter100', $element->getFilter('Filter100'));
        $this->assertInstanceOf('Foo_Bar_Validator200', $element->getValidator('Validator200'));
    }
}

class Alpha_Beta_Filter100 implements Zend_Filter_Interface
{
    public function filter($value)
    {
        return strval($value);
    }
}

class Foo_Bar_Validator200 implements Zend_Validate_Interface
{
    public function isValid($value)
    {
        return true;
    }

    public function getMessages()
    {
        return array();
    }
}
