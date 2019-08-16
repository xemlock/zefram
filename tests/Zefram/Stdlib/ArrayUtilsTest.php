<?php

class Zefram_Stdlib_ArrayUtilsTest extends PHPUnit_Framework_TestCase
{
    public function testMerge()
    {
        $a = array(
            'foo' => 1,
            'bar' => array(
                'a' => 0,
                'b' => true,
                'c',
            ),
            'baz',
            10
        );
        $b = array(
            10,
            'foo' => 2,
            'bar' => array(
                'd',
                'b' => false,
                'e' => array(
                    1000
                ),
            ),
            100
        );
        $this->assertSame(
            array(
                'foo' => 2,
                'bar' => array(
                    'a' => 0,
                    'b' => false,
                    'c',
                    'd',
                    'e' => array(
                        1000
                    ),
                ),
                'baz',
                10,
                10,
                100,
            ),
            Zefram_Stdlib_ArrayUtils::merge($a, $b)
        );
    }

    public function testMergePreserveNumericKeys()
    {
        $a = array(
            'foo' => 1,
            'bar' => array(
                'a' => 0,
                'b' => true,
                'c',
            ),
            'baz',
            10
        );
        $b = array(
            10,
            'foo' => 2,
            'bar' => array(
                'd',
                'b' => false,
                'e' => array(
                    1000
                ),
            ),
            100
        );
        $this->assertSame(
            array(
                'foo' => 2,
                'bar' => array(
                    'a' => 0,
                    'b' => false,
                    0 => 'c',
                    1 => 'd',
                    'e' => array(
                        1000
                    ),
                ),
                'baz',
                0 => 10,
                1 => 100,
            ),
            Zefram_Stdlib_ArrayUtils::merge($a, $b, true)
        );
    }
}
