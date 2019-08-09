<?php

class Zefram_Controller_ActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zefram_Controller_Action
     */
    protected $_controller;

    protected function setUp()
    {
        Zend_Controller_Action_HelperBroker::resetHelpers();
        $front = Zend_Controller_Front::getInstance();
        $front->resetInstance();
        $front->addControllerDirectory(__DIR__, 'default');

        $this->_controller = new Zefram_Controller_Action(
            new Zend_Controller_Request_Http(),
            new Zend_Controller_Response_Cli()
        );
    }

    public function tearDown()
    {
        unset($this->_controller);
    }

    public function testGetSingleParam()
    {
        $request = $this->_controller->getRequest();

        $request->setParam('foo', array('foo', 'bar', 'baz'));
        $this->assertEquals(array('foo', 'bar', 'baz'), $this->_controller->getParam('foo'));
        $this->assertEquals('foo', $this->_controller->getSingleParam('foo'));

        $request->setParam('bar', array());
        $this->assertEquals(array(), $this->_controller->getParam('bar'));
        $this->assertEquals('BAR', $this->_controller->getSingleParam('bar', 'BAR'));

        $request->setParam('baz', 'BAZ');
        $this->assertEquals('BAZ', $this->_controller->getParam('baz'));
        $this->assertEquals('BAZ', $this->_controller->getSingleParam('baz'));
    }
}
