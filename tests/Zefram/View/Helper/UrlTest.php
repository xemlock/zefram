<?php

class Zefram_View_Helper_UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_Controller_Front
     */
    public $front;

    /**
     * @var Zend_Controller_Router_Rewrite
     */
    public $router;

    /**
     * @var Zefram_View_Helper_Url
     */
    public $helper;

    protected function setUp()
    {
        $this->router = new Zend_Controller_Router_Rewrite();
        $this->router->addDefaultRoutes();
        $this->router->addRoutes(array(
            'archive' => new Zend_Controller_Router_Route(
                'archive/:year',
                array('year' => '2006', 'controller' => 'archive', 'action' => 'show'),
                array('year' => '\d+')
            ),
            'register' => new Zend_Controller_Router_Route(
                'register/:action',
                array('controller' => 'profile', 'action' => 'register')
            )
        ));

        $this->front = Zend_Controller_Front::getInstance();
        $this->front->setRouter($this->router);

        $this->helper = new Zefram_View_Helper_Url();
    }

    public function testDefaultEmpty()
    {
        $url = $this->helper->url();
        $this->assertEquals('/', $url);

        $url2 = $this->helper->url(null);
        $this->assertEquals('/', $url2);

        $url3 = $this->helper->url(array());
        $this->assertEquals('/', $url3);
    }

    public function testDefault()
    {
        $url = $this->helper->url(array('controller' => 'profile', 'action' => 'register'));
        $this->assertEquals('/profile/register', $url);
    }

    public function testNameAsFirstParam()
    {
        $url = $this->helper->url('archive');
        $this->assertEquals('/archive', $url);

        $url2 = $this->helper->url(array(), 'archive');
        $this->assertEquals('/archive', $url2);
    }

    public function testNameAsFirstParamWithOptions()
    {
        $url = $this->helper->url('archive', array('year' => 2008));
        $this->assertEquals('/archive/2008', $url);

        $url2 = $this->helper->url(array('year' => 2008), 'archive');
        $this->assertEquals('/archive/2008', $url2);
    }
}
