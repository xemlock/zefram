<?php

/**
 * @category   Zefram
 * @package    Zefram_Measure
 * @subpackage UnitTests
 */
class Zefram_MeasureTest extends TestCase
{
    protected function setUp()
    {
        Zend_Loader_Autoloader::getInstance()->suppressNotFoundWarnings(true);
        Zend_Locale_Data::disableCache(true);
    }

    public function testFactory()
    {
        $measure = Zefram_Measure::factory('Binary', 1024);
        $this->assertInstanceOf('Zend_Measure_Binary', $measure);
        $this->assertEquals(1024, $measure->getValue());

        $measure = Zefram_Measure::factory('Binary', '1024B');
        $this->assertInstanceOf('Zend_Measure_Binary', $measure);
        $this->assertEquals(Zend_Measure_Binary::BYTE, $measure->getType());
        $this->assertEquals(1024, $measure->getValue());

        $measure = Zefram_Measure::factory('Binary', '2048 B');
        $this->assertInstanceOf('Zend_Measure_Binary', $measure);
        $this->assertEquals(Zend_Measure_Binary::BYTE, $measure->getType());
        $this->assertEquals(2048, $measure->getValue());

        $measure = Zefram_Measure::factory('Flow_Volume', 100);
        $this->assertInstanceOf('Zend_Measure_Flow_Volume', $measure);
        $this->assertEquals(100, $measure->getValue());
    }

    public function testFactoryInvalidType()
    {
        $this->expectException('Zend_Loader_PluginLoader_Exception');

        Zefram_Measure::factory('Foo', 1024);
    }
}
