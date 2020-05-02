<?php

class Zefram_View_Helper_HeadMetaTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zefram_View_Helper_HeadMeta
     */
    protected $_helper;

    protected function setUp()
    {
        Zend_Registry::_unsetInstance();
        Zend_View_Helper_Placeholder_Registry::getRegistry()->deleteContainer('Zend_View_Helper_HeadMeta');

        $this->_helper = new Zefram_View_Helper_HeadMeta();
        $this->_helper->setView(new Zend_View());
    }

    public function testItemToString()
    {
        $this->_helper->view->doctype()->setDoctype(Zend_View_Helper_Doctype::HTML5);
        $this->_helper->appendProperty('og:locale', 'en_US');
        $this->assertEquals('<meta property="og:locale" content="en_US">', $this->_helper->toString());
    }
}
