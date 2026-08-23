<?php

class Zefram_ValidateTest extends TestCase
{
    public function testSetTranslator()
    {
        $validator = new Zefram_Validate();
        $validator->addValidator('greaterThan', true, array('min' => 1024));

        $validators = $validator->getValidators();
        $this->assertInstanceOf('Zend_Validate_GreaterThan', $validators[0]);

        $validator->setTranslator(
            new Zend_Translate_Adapter_Array(
                array(
                    'locale' => 'en',
                    'content' => array(
                        Zend_Validate_GreaterThan::NOT_GREATER => 'greaterThan validation failed',
                    ),
                )
            )
        );

        $this->assertFalse($validator->isValid(1000));
        $this->assertContains('greaterThan validation failed', $validator->getMessages());
    }
}
