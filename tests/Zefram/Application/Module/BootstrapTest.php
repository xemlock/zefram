<?php

class Zefram_Application_Module_BootstrapTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zefram_Application
     */
    protected $_application;

    /**
     * @var Zefram_Application_Module_Bootstrap
     */
    protected $_moduleBootstrap;

    public function setUp()
    {
        $this->_application = new Zefram_Application('test');
        $this->_moduleBootstrap = new Test_ModuleBootstrap($this->_application);
    }

    public function testConstructor()
    {
        $application = $this->_application;
        $moduleBootstrap = $this->_moduleBootstrap;

        $this->assertSame($moduleBootstrap->getApplication(), $application->getBootstrap());
        $this->assertSame($moduleBootstrap->getPluginLoader(), $application->getBootstrap()->getPluginLoader());
    }

    public function testHasClassResource()
    {
        $moduleBootstrap = $this->_moduleBootstrap;

        $this->assertTrue($moduleBootstrap->hasClassResource('foo'));
        $this->assertTrue($moduleBootstrap->hasClassResource('bar'));
        $this->assertFalse($moduleBootstrap->hasClassResource('qux'));
    }

    public function testGetModuleDirectory()
    {
        $moduleBootstrap = $this->_moduleBootstrap;
        $this->assertEquals(dirname(__FILE__), $moduleBootstrap->getModuleDirectory());
    }

    public function testOptions()
    {
        $application = new Zefram_Application('test', array(
            'test' => array(
                'foo' => 'foo',
            ),
            'Test' => array(
                'bar' => 'bar',
            ),
            'TEST' => array(
                'foo' => 'baz',
            ),
        ));
        $moduleBootstrap = new Test_ModuleBootstrap($application);

        $this->assertEquals(array(
            'foo' => 'baz',
            'bar' => 'bar',
        ), $moduleBootstrap->getOptions());
    }
}

class Test_ModuleBootstrap extends Zefram_Application_Module_Bootstrap
{
    protected function _initFoo()
    {
        return (object) array('name' => 'foo');
    }

    protected function _initBar()
    {
        return (object) array('name' => 'bar');
    }

    protected function _initBaz()
    {
        return (object) array('name' => 'baz');
    }
}
