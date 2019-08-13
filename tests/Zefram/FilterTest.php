<?php

class Zefram_FilterTest extends PHPUnit_Framework_TestCase
{
    public function testAddFilter()
    {
        $filter = new Zefram_Filter();

        $filter->addFilter('Int');
        $filter->addFilter('Int');

        $this->assertEquals(2, count($filter->getFilters()));
        $this->assertInstanceOf('Zend_Filter_Int', $filter->getFilter(0));
        $this->assertInstanceOf('Zend_Filter_Int', $filter->getFilter(1));

        $this->assertInstanceOf('Zend_Filter_Int', $filter->getFilter('Int'));
        $this->assertSame($filter->getFilter(0), $filter->getFilter('Int'));

        $this->assertSame(1024, $filter->filter('1024'));
    }

    public function testAddFilters()
    {
        $filter = new Zefram_Filter();

        $filter->addFilters(array(
            'Int',
            array('Int'),
            new Zend_Filter_Int(),
        ));

        $this->assertEquals(3, count($filter->getFilters()));
        $this->assertInstanceOf('Zend_Filter_Int', $filter->getFilter(0));
        $this->assertInstanceOf('Zend_Filter_Int', $filter->getFilter(1));
        $this->assertInstanceOf('Zend_Filter_Int', $filter->getFilter(2));

        $this->assertInstanceOf('Zend_Filter_Int', $filter->getFilter('Int'));
        $this->assertSame($filter->getFilter(0), $filter->getFilter('Int'));

        $this->assertSame(1024, $filter->filter('1024'));
    }
}
