<?php

class Zefram_Application_Bootstrap_BootstrapTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return Zend_Application
     */
    public function createApplication()
    {
        $ref = new ReflectionClass('Zefram_Application_Bootstrap_Bootstrap');
        $app = new Zend_Application('development', array(
            'bootstrap' => array(
                'path' => $ref->getFileName(),
                'class' => 'Zefram_Application_Bootstrap_Bootstrap',
            ),
            'resources' => array(
                'frontController' => array(
                    'moduleDirectory'               => dirname(__FILE__) . '/../../../assets/modules',
                    'moduleControllerDirectoryName' => 'controllers',
                ),
                'modules' => true,
            )
        ));
        return $app;
    }

    public function testProxyResource()
    {
        $obj = new ResourceTestClass;

        $app = $this->createApplication();
        $bootstrap = $app->getBootstrap();

        // add new plugin resources -> TODO can also be done using registerPluginResource()
        $bootstrap->setOptions(array(
            'resources' => array(
                'powerLevel' => 9000,
                'obj' => $obj,
            ),
        ));

        // proxy resources should be present in the container even when the application is
        // not yet bootstrapped
        // by definition - proxy resources do not need bootstrapping hence they are
        // available as soon as they are registered in bootstrap

        $resource = $bootstrap->getPluginResource('powerLevel');
        $this->assertInstanceOf('Zefram_Application_Resource_ContainerData', $resource);
        $this->assertEquals($bootstrap->getContainer()->{'powerLevel'}, 9000);

        $resource = $bootstrap->getPluginResource('obj');
        $this->assertInstanceOf('Zefram_Application_Resource_ContainerData', $resource);
        $this->assertSame($bootstrap->getContainer()->{'obj'}, $obj);

        $app->bootstrap();

        $this->assertEquals($bootstrap->getContainer()->{'powerLevel'}, 9000);
        $this->assertSame($bootstrap->getContainer()->{'obj'}, $obj);
    }
}

class ResourceTestClass
{}