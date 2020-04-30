<?php

class Zefram_View_Helper_HeadStyleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zefram_View_Helper_HeadStyle
     */
    protected $_helper;

    protected function setUp()
    {
        Zend_View_Helper_Placeholder_Registry::getRegistry()->deleteContainer('Zend_View_Helper_HeadStyle');

        $this->_helper = new Zefram_View_Helper_HeadStyle();
        $this->_helper->setView(new Zend_View());
    }

    public function testItemToString()
    {
        $this->_helper->appendStyle('* { box-sizing: border-box; }', array('noescape' => true));
        $this->assertEquals(
            '<style type="text/css" media="all">' . PHP_EOL . '* { box-sizing: border-box; }' . PHP_EOL . '</style>',
            $this->_helper->toString()
        );
    }
}
