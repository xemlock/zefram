<?php

class Zefram_ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $config = Zefram_Config::factory(array(
            'foo' => array(
                'bar' => 'baz',
            ),
        ));

        $this->assertInstanceOf('Zend_Config', $config);
        $this->assertTrue(isset($config->foo->bar));
        $this->assertEquals('baz', $config->foo->bar);
    }
}
