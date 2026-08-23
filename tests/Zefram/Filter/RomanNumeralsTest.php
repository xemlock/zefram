<?php

class Zefram_Filter_RomanNumeralsTest extends TestCase
{
    public function testConvertsInteger()
    {
        $this->assertSame('MMCXXXVII', Zefram_Filter_RomanNumerals::filterStatic(2137));
    }

    public function testConvertsIntegerUsingSubtractivePairs()
    {
        $this->assertSame('MCMXCIV', Zefram_Filter_RomanNumerals::filterStatic(1994));
    }

    public function testNonPositiveValueReturnsN()
    {
        $this->assertSame('N', Zefram_Filter_RomanNumerals::filterStatic(0));
        $this->assertSame('N', Zefram_Filter_RomanNumerals::filterStatic(-42));
    }

    public function testFilterMethodDelegatesToStatic()
    {
        $filter = new Zefram_Filter_RomanNumerals();
        $this->assertSame('XLII', $filter->filter(42));
    }
}
