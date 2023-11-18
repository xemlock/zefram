<?php

class Zefram_View_Helper_HeadLinkTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zefram_View_Helper_HeadLink
     */
    protected $_helper;

    protected function setUp()
    {
        Zend_Registry::_unsetInstance();
        Zend_View_Helper_Placeholder_Registry::getRegistry()->deleteContainer('Zend_View_Helper_HeadLink');

        $this->_helper = new Zefram_View_Helper_HeadLink();
        $this->_helper->setView(new Zend_View());
    }

    public function testItemToString()
    {
        $this->_helper->appendStylesheet('foo.css');
        $this->assertEquals('<link href="foo.css" media="all" rel="stylesheet" type="text/css">', $this->_helper->toString());
    }

    public function testItemToStringXhtml()
    {
        $this->_helper->appendStylesheet('foo.css');
        $this->_helper->view->doctype(Zend_View_Helper_Doctype::XHTML1_STRICT);

        $this->assertEquals('<link href="foo.css" media="all" rel="stylesheet" type="text/css"/>', $this->_helper->toString());
    }

    public function testToStringWithInvalidItems()
    {
        $this->_helper->appendStylesheet('foo.css');
        $this->_helper->getContainer()->append((object) array('foo' => 'bar'));
        $this->_helper->appendAlternate(array('href' => 'http://example.com/', 'hreflang' => 'x-default'));
        $this->assertEquals(
            '<link href="foo.css" media="all" rel="stylesheet" type="text/css">'
            . PHP_EOL
            . '<link href="http://example.com/" hreflang="x-default">',
            $this->_helper->toString()
        );

        $indent = '    ';
        $this->assertEquals(
            $indent . '<link href="foo.css" media="all" rel="stylesheet" type="text/css">'
            . PHP_EOL
            . $indent . '<link href="http://example.com/" hreflang="x-default">',
            $this->_helper->toString($indent)
        );
    }

    public function testCreateDataStylesheet()
    {
        $this->assertEquals(
            array(
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => 'style.css',
                'media' => 'all',
                'conditionalStylesheet' => false,
                'extras' => array(),
            ),
            get_object_vars(
                $this->_helper->createDataStylesheet(array(
                    'style.css',
                ))
            )
        );

        $this->assertEquals(
            array(
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => 'style.css',
                'media' => 'screen',
                'conditionalStylesheet' => 'IE 6',
                'extras' => array(),
            ),
            get_object_vars(
                $this->_helper->createDataStylesheet(array(
                    'style.css',
                    'screen',
                    'IE 6',
                    'extras',
                ))
            )
        );

        $this->assertEquals(
            array(
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => 'style.css',
                'media' => 'screen',
                'conditionalStylesheet' => 'IE 6',
                'extras' => array(
                    'id' => 'style-css',
                ),
            ),
            get_object_vars(
                $this->_helper->createDataStylesheet(array(
                    'foo' => 'style.css',
                    'bar' => 'screen',
                    'baz' => 'IE 6',
                    'qux' => array(
                        'id' => 'style-css',
                    ),
                ))
            )
        );
    }
}
