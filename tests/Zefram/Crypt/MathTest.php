<?php

class Zefram_Crypt_MathTest extends PHPUnit_Framework_TestCase
{
    public function testRandInteger()
    {
        $int = Zefram_Crypt_Math::randInteger(10, 100);
        $this->assertInternalType('integer', $int);
        $this->assertGreaterThanOrEqual(10, $int);
        $this->assertLessThanOrEqual(100, $int);
    }

    public function testRandIntegerNoArgs()
    {
        $int = Zefram_Crypt_Math::randInteger();
        $this->assertInternalType('integer', $int);
        $this->assertGreaterThanOrEqual(0, $int);
        $this->assertLessThanOrEqual(mt_getrandmax(), $int);
    }

    public function testRandFloat()
    {
        $float = Zefram_Crypt_Math::randFloat();
        $this->assertInternalType('float', $float);
        $this->assertGreaterThanOrEqual(0, $float);
        $this->assertLessThan(1, $float);
    }

    public function testRandString()
    {
        $string = Zefram_Crypt_Math::randString(10);
        $this->assertInternalType('string', $string);
        $this->assertEquals(10, strlen($string));
    }
}
