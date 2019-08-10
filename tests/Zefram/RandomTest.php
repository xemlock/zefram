<?php

class Zefram_RandomTest extends PHPUnit_Framework_TestCase
{
    public function testGetInteger()
    {
        $int = Zefram_Random::getInteger(10, 100);
        $this->assertGreaterThanOrEqual(10, $int);
        $this->assertLessThanOrEqual(100, $int);
    }

    public function testGetFloat()
    {
        $float = Zefram_Random::getFloat();
        $this->assertGreaterThanOrEqual(0, $float);
        $this->assertLessThan(1, $float);
    }

    public function testGetString()
    {
        $string = Zefram_Random::getString(10);
        $this->assertEquals(10, strlen($string));
    }
}
