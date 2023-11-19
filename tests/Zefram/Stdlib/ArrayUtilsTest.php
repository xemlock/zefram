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

        $this->assertSame($a, Zefram_Stdlib_ArrayUtils::merge($a, 'Foo'));
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

    public function testReduce()
    {
        $input = array(
            'foo' => 1,
            'bar' => 2,
            'baz' => 3,
            'qux' => 4,
        );

        $this->assertEquals(
            10,
            Zefram_Stdlib_ArrayUtils::reduce(
                $input,
                array(__CLASS__, 'reduceCallbackSumValues'),
                0
            )
        );

        $this->assertEquals(
            'foo,bar,baz,qux',
            Zefram_Stdlib_ArrayUtils::reduce(
                $input,
                array(__CLASS__, 'reduceCallbackJoinKeys'),
                ''
            )
        );
    }

    public static function reduceCallbackSumValues($acc, $value)
    {
        return $acc + $value;
    }

    public static function reduceCallbackJoinKeys($acc, $value, $key)
    {
        return strlen($acc) ? $acc . ',' . $key : $key;
    }
}
