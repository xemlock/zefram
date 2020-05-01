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
        $this->_helper->appendStyle('a:before { content: "</style>"; }', array('noescape' => true));

        $this->assertEquals(
            '<style type="text/css" media="all">' . PHP_EOL
            . '* { box-sizing: border-box; }' . PHP_EOL
            . '</style>' . PHP_EOL
            . '<style type="text/css" media="all">' . PHP_EOL
            . 'a:before { content: "<\/style>"; }' . PHP_EOL
            . '</style>',
            $this->_helper->toString()
        );
    }

    public function testToStringWithInvalidItems()
    {
        $this->_helper->appendStyle('* { box-sizing: border-box; }', array('noescape' => true));
        $this->_helper->appendStyle('');
        $this->_helper->appendStyle('', array('noescape' => true));
        $this->_helper->appendStyle('html, body { min-height: 100%; }', array('noescape' => true));
        $this->_helper->getContainer()->append((object) array('foo' => 'bar'));

        $this->assertEquals(
            '<style type="text/css" media="all">' . PHP_EOL
            . '* { box-sizing: border-box; }' . PHP_EOL
            . '</style>' . PHP_EOL
            . '<style type="text/css" media="all">' . PHP_EOL
            . 'html, body { min-height: 100%; }' . PHP_EOL
            . '</style>',
            $this->_helper->toString()
        );

        $indent = '    ';
        $this->assertEquals(
            $indent . '<style type="text/css" media="all">' . PHP_EOL
            . $indent . $indent . '* { box-sizing: border-box; }' . PHP_EOL
            . $indent . '</style>' . PHP_EOL
            . $indent . '<style type="text/css" media="all">' . PHP_EOL
            . $indent . $indent . 'html, body { min-height: 100%; }' . PHP_EOL
            . $indent . '</style>',
            $this->_helper->toString($indent)
        );
    }
}
