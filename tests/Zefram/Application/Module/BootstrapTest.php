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
        $this->assertSame($moduleBootstrap->getContainer(), $application->getBootstrap()->getContainer());
    }

    public function testHasClassResource()
    {
        $moduleBootstrap = $this->_moduleBootstrap;

        $this->assertTrue($moduleBootstrap->hasClassResource('foo'));
        $this->assertTrue($moduleBootstrap->hasClassResource('bar'));
        $this->assertFalse($moduleBootstrap->hasClassResource('qux'));
    }

    public function testBootstrapResource()
    {
        $foo = $this->_moduleBootstrap->bootstrapResource('foo');

        $this->assertInstanceOf('stdClass', $foo);
        $this->assertEquals('foo', $foo->name);
    }

    public function testBootstrapResources()
    {
        list($foo, $bar) = $this->_moduleBootstrap->bootstrapResources('foo', 'bar');

        $this->assertInstanceOf('stdClass', $foo);
        $this->assertEquals('foo', $foo->name);

        $this->assertInstanceOf('stdClass', $bar);
        $this->assertEquals('bar', $bar->name);
    }

    public function testBootstrapResourcesWithArray()
    {
        list($bar, $baz) = $this->_moduleBootstrap->bootstrapResources(array('bar', 'baz'));

        $this->assertInstanceOf('stdClass', $bar);
        $this->assertEquals('bar', $bar->name);

        $this->assertInstanceOf('stdClass', $baz);
        $this->assertEquals('baz', $baz->name);
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
