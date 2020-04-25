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

    public function testDropdown()
    {
        $navigation = new Zend_Navigation(array(
            array(
                'uri'         => '',
                'element'     => 'button',
                'label'       => 'Dropdown <i class="caret"></i>',
                'id'          => 'dropdownMenuLink',
                'class'       => 'dropdown-toggle',
                'liClass'     => 'dropdown',
                'escapeLabel' => false,
                'customHtmlAttribs' => array(
                    'data-toggle' => 'dropdown',
                ),
                'ulClass' => 'dropdown-menu',
                'ulId'    => 'dropdownMenu',
                'ulHtmlAttribs' => array(
                    'aria-labelledby' => 'dropdownMenuLink',
                ),
                'pages'   => array(
                    array(
                        'label' => 'Page 1',
                        'uri'   => 'page1',
                    ),
                    array(
                        'label' => 'Page 2',
                        'uri'   => 'page2',
                    ),
                ),
            ),
        ));

        $this->assertSame(
            $this->_getExpected('menu/dropdown.html'),
            $this->_helper->skipPrefixForId(true)->render($navigation)
        );
    }

    public function testPagePartial()
    {
        $navigation = new Zend_Navigation(array(
            array(
                'partial' => 'menu/page_partial.phtml',
                'label'   => 'Partial',
                'uri'     => '',
            ),
        ));

        $this->_helper->view->addScriptPath(dirname(__FILE__) . '/_files/views');

        $this->assertSame(
            $this->_getExpected('menu/page_partial.html'),
            $this->_helper->render($navigation)
        );
    }

    public function testPageRender()
    {
        $navigation = new Zend_Navigation(array(
            new Zefram_View_Helper_Navigation_MenuTest_Page(),
        ));

        $this->_helper->view->addScriptPath(dirname(__FILE__) . '/_files/views');

        $this->assertSame(
            $this->_getExpected('menu/page_render.html'),
            $this->_helper->render($navigation)
        );
    }
}

class Zefram_View_Helper_Navigation_MenuTest_Page extends Zend_Navigation_Page_Uri
{
    public function render(Zend_View_Abstract $view)
    {
        return '<div><img src="https://www.gravatar.com/avatar?d=mm"> John Doe</div>';
    }
}
