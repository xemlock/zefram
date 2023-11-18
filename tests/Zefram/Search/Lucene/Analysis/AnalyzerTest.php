<?php

class Zefram_Search_Lucene_Analysis_AnalyzerTest extends PHPUnit_Framework_TestCase
{
    public function testAddFiltersAsInstance()
    {
        $lowerCase = new Zefram_Search_Lucene_Analysis_TokenFilter_LowerCase();

        $analyzer = new Zefram_Search_Lucene_Analysis_Analyzer();
        $analyzer->setFilters(array($lowerCase));

        $filters = $analyzer->getFilters();
        $this->assertEquals(1, count($filters));
        $this->assertSame($lowerCase, $filters[0]);
    }

    public function testAddFiltersAsString()
    {
        $analyzer = new Zefram_Search_Lucene_Analysis_Analyzer();
        $analyzer->setFilters(array('lowerCase'));

        $filters = $analyzer->getFilters();
        $this->assertEquals(1, count($filters));

        $this->assertInstanceOf('Zefram_Search_Lucene_Analysis_TokenFilter_LowerCase', $filters[0]);
    }

    public function testAddFiltersAsFilterOptionsMap()
    {
        $analyzer = new Zefram_Search_Lucene_Analysis_Analyzer();
        $analyzer->setFilters(array(
            array(
                'filter'  => 'lowerCase',
                'options' => array('encoding' => 'ISO-8859-1'),
            ),
        ));

        $filters = $analyzer->getFilters();
        $this->assertEquals(1, count($filters));

        /** @var Zefram_Search_Lucene_Analysis_TokenFilter_LowerCase $filter */
        list($filter) = $filters;

        $this->assertInstanceOf('Zefram_Search_Lucene_Analysis_TokenFilter_LowerCase', $filter);
        $this->assertEquals('ISO-8859-1', $filter->getEncoding());
    }

    public function testAddFiltersAsFilterOptionsList()
    {
        $analyzer = new Zefram_Search_Lucene_Analysis_Analyzer();
        $analyzer->setFilters(array(
            array('lowerCase', array('encoding' => 'ISO-8859-1')),
        ));

        $filters = $analyzer->getFilters();
        $this->assertEquals(1, count($filters));

        /** @var Zefram_Search_Lucene_Analysis_TokenFilter_LowerCase $filter */
        list($filter) = $filters;

        $this->assertInstanceOf('Zefram_Search_Lucene_Analysis_TokenFilter_LowerCase', $filter);
        $this->assertEquals('ISO-8859-1', $filter->getEncoding());
    }

    /**
     * @see https://www.php.net/manual/en/migration73.incompatible.php#migration73.incompatible.core.continue-targeting-switch
     */
    public function testAddFiltersEmptySpec()
    {
        $analyzer = new Zefram_Search_Lucene_Analysis_Analyzer();
        $analyzer->setFilters(array(
            array(),
            array('lowerCase'),
        ));

        $this->assertCount(1, $analyzer->getFilters());
    }
}
