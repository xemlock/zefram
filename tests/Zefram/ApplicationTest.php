<?php

class Zefram_ApplicationTest extends PHPUnit_Framework_TestCase
{
    public function testBootstrapClassWithSetter()
    {
        $application = new Zefram_Application('development');
        $application->setBootstrapClass('Zefram_ApplicationTest_Bootstrap');

        $this->assertSame('Zefram_ApplicationTest_Bootstrap', get_class($application->getBootstrap()));
    }

    public function testBootstrapClassFromOptions()
    {
        $application = new Zefram_Application('development', array(
            'bootstrapClass' => 'Zefram_ApplicationTest_Bootstrap',
        ));

        $this->assertSame('Zefram_ApplicationTest_Bootstrap', get_class($application->getBootstrap()));
    }

    public function testBootstrapClassFromConfig()
    {
        $environment = 'development';

        $config = new Zend_Config_Xml(<<<END
<?xml version="1.0"?>
<config xmlns:zf="http://framework.zend.com/xml/zend-config-xml/1.0/">
    <development>
        <bootstrapClass>Zefram_ApplicationTest_Bootstrap</bootstrapClass>
    </development>
</config>
END
        , $environment);

        $application = new Zefram_Application($environment, $config);

        $this->assertSame('Zefram_ApplicationTest_Bootstrap', get_class($application->getBootstrap()));
    }

}

class Zefram_ApplicationTest_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{}
