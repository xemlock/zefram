<?php

class Zefram_Search_LuceneTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_clearDirectory(dirname(__FILE__) . '/_index');
    }

    public function tearDown()
    {
        $this->_clearDirectory(dirname(__FILE__) . '/_index');
    }

    /**
     * Test for demonstration of Zend_Search_Lucene design flaw, a singleton
     * default analyzer. Default analyzer should be referenced only upon object
     * creation and be immutable, otherwise changing it may lead to index corruption.
     */
    public function testZFLuceneDesignFlaw()
    {
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum()
        );

        $index = Zend_Search_Lucene::create(dirname(__FILE__) . '/_index/' . __FUNCTION__);

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::text('text', '1024', 'utf-8'));
        $index->addDocument($doc);

        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Text()
        );

        // Default analyzer is now changed. Document added to index will now use
        // new analyzer making index corrupted in terms of input data processing.
        // Index should be agnostic of analyzer changes, as there can be more
        // than one index in the application, and each one is expected to have its
        // own analyzer.

        $doc2 = new Zend_Search_Lucene_Document();
        $doc2->addField(Zend_Search_Lucene_Field::text('text', '1024 2048', 'utf-8'));

        // Both documents should be present in query results, but because of the
        // change of the default analyzer only the first document is returned.
        $this->assertEquals(1, count($index->find('1024')));
    }

    public function testIndexAnalyzer()
    {
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum()
        );

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::text('text', '1024', 'utf-8'));

        $index = Zefram_Search_Lucene::create(dirname(__FILE__) . '/_index/' . __FUNCTION__);
        $index->addDocument($doc);

        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Text()
        );

        $doc2 = new Zend_Search_Lucene_Document();
        $doc2->addField(Zend_Search_Lucene_Field::text('text', '1024 2048', 'utf-8'));
        $index->addDocument($doc);

        $this->assertEquals(2, count($index->find('1024')));
    }

    public function testIndexGetAnalyzer()
    {
        $analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_Text();

        Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzer);

        $index = new Zefram_Search_Lucene(dirname(__FILE__) . '/_index/' . __FUNCTION__, true);

        $this->assertSame($analyzer, $index->getAnalyzer());

        $analyzer2 = new Zend_Search_Lucene_Analysis_Analyzer_Common_Text();
        Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzer2);

        $this->assertNotSame($analyzer2, $index->getAnalyzer());
    }

    private function _clearDirectory($dirName)
    {
        if (!file_exists($dirName) || !is_dir($dirName))  {
            return;
        }
        // remove files from temporary directory
        $dir = opendir($dirName);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (!is_dir($dirName . '/' . $file)) {
                @unlink($dirName . '/' . $file);
            } else {
                $this->_clearDirectory($dirName . '/' . $file);
            }
        }
        closedir($dir);
        @rmdir($dirName);
    }
}
