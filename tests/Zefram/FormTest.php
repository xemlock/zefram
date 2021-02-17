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
}
