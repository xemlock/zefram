<?php

class Zefram_Log_Filter_MessageTest extends PHPUnit_Framework_TestCase
{
    public function testInvalidRegularExpression()
    {
        try {
            $filter = new Zefram_Log_Filter_Message('invalid regexp');
            $this->fail();
        } catch (Exception $e) {
            $this->assertTrue($e instanceof Zend_Log_Exception);
            $this->assertRegexp('/invalid regexp/i', $e->getMessage());
        }
    }

    public function testAccept()
    {
        $filter = new Zefram_Log_Filter_Message('/accept/');
        $this->assertTrue($filter->accept(array('message' => 'foo accept bar')));
        $this->assertFalse($filter->accept(array('message' => 'foo reject bar')));
    }

    public function testInvertedAccept()
    {
        $filter = new Zefram_Log_Filter_Message('/reject/', true);
        $this->assertTrue($filter->accept(array('message' => 'foo accept bar')));
        $this->assertFalse($filter->accept(array('message' => 'foo reject bar')));
    }

    public function testFactory()
    {
        $filter = Zefram_Log_Filter_Message::factory(array(
            'regexp' => '/reject/',
            'invert' => true,
        ));
        $this->assertTrue($filter instanceof Zefram_Log_Filter_Message);
        $this->assertTrue($filter->accept(array('message' => 'foo accept bar')));
        $this->assertFalse($filter->accept(array('message' => 'foo reject bar')));
    }
}
