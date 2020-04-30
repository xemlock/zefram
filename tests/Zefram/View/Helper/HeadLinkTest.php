<?php

class Zefram_View_Helper_HeadLinkTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zefram_View_Helper_HeadLink
     */
    protected $_helper;

    protected function setUp()
    {
        Zend_View_Helper_Placeholder_Registry::getRegistry()->deleteContainer('Zend_View_Helper_HeadLink');

        $this->_helper = new Zefram_View_Helper_HeadLink();
        $this->_helper->setView(new Zend_View());
    }

    public function testItemToString()
    {
        $this->_helper->appendStylesheet('foo.css');
        $this->assertEquals('<link href="foo.css" media="all" rel="stylesheet" type="text/css">', $this->_helper->toString());
    }
}
