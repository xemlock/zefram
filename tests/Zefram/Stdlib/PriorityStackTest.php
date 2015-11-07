<?php

class Zefram_Stdlib_PriorityStackTest extends PHPUnit_Framework_TestCase
{
    public function testPush()
    {
        $stack = new Zefram_Stdlib_PriorityStack();
        $stack->push(1, 'first');
        $stack->push(2, 'second');
        $stack->push(3, 'third');

        $result = array();
        foreach ($stack as $item) {
            $result[] = $item;
        }
        $this->assertEquals(array(3, 2, 1), $result);
        $this->assertEquals(array('first', 'second', 'third'), array_keys($stack->getItemsByName()));
    }
}