<?php

class Zefram_View_Helper_Navigation_MenuTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zefram_View_Helper_Navigation_Menu
     */
    protected $_helper;

    /**
     * @var Zend_Navigation
     */
    protected $_navigation;

    protected function setUp()
    {
        $config = require dirname(__FILE__) . '/_files/navigation.php';
        $this->_navigation = new Zend_Navigation($config);

        $this->_helper = new Zefram_View_Helper_Navigation_Menu();
        $this->_helper->setView(new Zend_View());
        $this->_helper->setContainer($this->_navigation);
    }

    /**
     * Returns the contents of the expected file
     *
     * @param string $file
     * @return string
     */
    protected function _getExpected($file)
    {
        return rtrim(file_get_contents(dirname(__FILE__) . '/_files/expected/' . $file), "\n");
    }

    public function testRenderMenu()
    {
        $this->assertSame(
            $this->_getExpected('menu/default.html'),
            $this->_helper->render()
        );
    }

    public function testRenderDeepestMenu()
    {
        $this->assertSame(
            $this->_getExpected('menu/onlyactivebranch_noparents.html'),
            $this->_helper->setOnlyActiveBranch(true)->setRenderParents(false)->render()
        );
    }

    public function testHtmlAttribs()
    {
        // create menu separator and insert it after the first page
        $pages = array();
        foreach ($this->_navigation->getPages() as $page) {
            $pages[] = $page;
        }

        $separator = new Zend_Navigation_Page_Uri();
        $separator->set('liClass', 'separator');
        $separator->set('liHtmlAttribs', array('role' => 'separator'));
        $this->_navigation->addPage($separator);

        array_splice($pages, 1, 0, array($separator));

        foreach ($pages as $order => $page) {
            $page->setOrder($order);
        }

        $this->assertSame(
            $this->_getExpected('menu/htmlattribs.html'),
            $this->_helper->setUlHtmlAttribs(array(
                'role' => 'navigation',
                'aria-label' => 'Main',
            ))->render()
        );
    }
}
