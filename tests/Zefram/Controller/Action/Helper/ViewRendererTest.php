<?php

class Zefram_Controller_Action_Helper_ViewRendererTest extends PHPUnit_Framework_TestCase
{
    public function testAddHelper()
    {
        $viewRenderer = new Zefram_Controller_Action_Helper_ViewRenderer();
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        $this->assertSame($viewRenderer, Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer'));
    }

    protected function _testModuleDependentOption($option)
    {
        $getter = 'get' . $option;
        $setter = 'set' . $option;

        $viewRenderer = new Zefram_Controller_Action_Helper_ViewRenderer();

        $viewRenderer->$setter('shared-value');
        $viewRenderer->$setter('module-value', 'module');

        $request = new Zend_Controller_Request_Http();

        $viewRenderer->setActionController(null);
        $viewRenderer->getFrontController()->setRequest($request);

        $this->assertEquals('shared-value', $viewRenderer->$getter());
        $this->assertEquals('shared-value', $viewRenderer->$getter('other-module'));
        $this->assertEquals('module-value', $viewRenderer->$getter('module'));

        $request->setModuleName('module');
        $this->assertEquals('module-value', $viewRenderer->$getter());
        $this->assertEquals('module-value', $viewRenderer->$getter('module'));

        $request->setModuleName('other-module');
        $this->assertEquals('shared-value', $viewRenderer->$getter());
        $this->assertEquals('module-value', $viewRenderer->$getter('module'));
    }

    public function testViewBasePathSpec()
    {
        $this->_testModuleDependentOption('viewBasePathSpec');
    }

    public function testViewScriptPathSpec()
    {
        $this->_testModuleDependentOption('viewScriptPathSpec');
    }

    public function testViewScriptPathNoControllerSpec()
    {
        $this->_testModuleDependentOption('viewScriptPathNoControllerSpec');
    }

    public function testViewSuffix()
    {
        $this->_testModuleDependentOption('viewSuffix');
    }
}
