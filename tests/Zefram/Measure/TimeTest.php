<?php

/**
 * @category   Zefram
 * @package    Zefram_Measure
 * @subpackage UnitTests
 */
class Zefram_Measure_TimeTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Zend_Loader_Autoloader::getInstance()->suppressNotFoundWarnings(true);

        $cache = new Zend_Cache_Core();
        @$cache->setBackend(new Zend_Cache_Backend_BlackHole());
        Zend_Locale_Data::setCache($cache);
    }

    public function testConstructor()
    {
        $measure = new Zefram_Measure_Time('01:00');
        $this->assertEquals(Zefram_Measure_Time::SECOND, $measure->getType());
        $this->assertEquals(3600, $measure->getValue());

        $measure = new Zefram_Measure_Time('01:00', Zefram_Measure_Time::HOUR);
        $this->assertEquals(1, $measure->getValue());
    }

    public function testSetValue()
    {
        $measure = new Zefram_Measure_Time(null);
        $measure->setValue('02:00');
        $this->assertEquals(Zefram_Measure_Time::SECOND, $measure->getType());
        $this->assertEquals(7200, $measure->getValue());

        $measure = new Zefram_Measure_Time(null);
        $measure->setValue('02:00', Zefram_Measure_Time::HOUR);
        $this->assertEquals(2, $measure->getValue());
    }

    public function testFactory()
    {
        $measure = Zefram_Measure::factory('Time', 0);
        $this->assertInstanceOf('Zefram_Measure_Time', $measure);
    }
}
