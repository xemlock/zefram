<?php

require_once __DIR__ . '/assets/providers.php';
require_once __DIR__ . '/assets/namespaced_providers.php';

class Zefram_Tool_Framework_Provider_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param Zefram_Tool_Framework_Provider_Abstract $provider
     * @param string $expectedName
     * @dataProvider getNameProvider
     */
    public function testGetName(Zefram_Tool_Framework_Provider_Abstract $provider, $expectedName)
    {
        $this->assertEquals($expectedName, $provider->getName());
    }

    public function getNameProvider()
    {
        return array(
            array(new FooProvider, 'Foo'),
            array(new Foo_BarProvider, 'Bar'),
            array(new Foo_Bar_BazProvider, 'Baz'),
            array(new Provider, 'Provider'),
            array(new \foo\bar\FooProvider, 'Foo'),
            array(new \foo\bar\Foo_BarProvider, 'Bar'),
            array(new \foo\bar\Foo_Bar_BazProvider, 'Baz'),
            array(new \foo\bar\Provider, 'Provider'),
        );
    }
}
