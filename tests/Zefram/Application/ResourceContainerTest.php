<?php

class Zefram_Application_ResourceContainerTest extends PHPUnit_Framework_TestCase
{
    protected $_container;

    public function getContainer()
    {
        if ($this->_container === null) {
            $this->_container = new Zefram_Application_ResourceContainer();
        }
        return $this->_container;
    }

    public function testResourceCallback()
    {
        $container = $this->getContainer();
        $callback = array($this, 'resourceCallback');

        $container->addResourceCallback('pokemon', $callback, array('Gyarados'));
        $resource = $container->getResource('pokemon');

        $this->assertInstanceOf('Res', $resource);
        $this->assertEquals('Gyarados', $resource->getName());

        $callback = new Zefram_Stdlib_CallbackHandler(array($this, 'resourceCallback'), array(), array('Lugia'));
        $container->addResourceCallback('pokemon2', $callback);
        $resource = $container->getResource('pokemon2');

        $this->assertInstanceOf('Res', $resource);
        $this->assertEquals('Lugia', $resource->getName());
    }

    public function resourceCallback($name)
    {
        return new Res($name);
    }
}

class Res
{
    public function __construct($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }
}