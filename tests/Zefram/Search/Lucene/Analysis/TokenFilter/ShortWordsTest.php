<?php

class Zefram_Search_Lucene_Analysis_TokenFilter_ShortWordsTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorOptions()
    {
        $filter = new Zefram_Search_Lucene_Analysis_TokenFilter_ShortWords(array(
            'minLength' => 3,
            'encoding' => 'ISO-8859-1',
        ));

        $this->assertEquals(3, $filter->getMinLength());
        $this->assertEquals('ISO-8859-1', $filter->getEncoding());
    }

    public function testConstructorOptionsBackwardCompatible()
    {
        $filter = new Zefram_Search_Lucene_Analysis_TokenFilter_ShortWords(3);

        $this->assertEquals(3, $filter->getMinLength());
    }

    /**
     * Test for demonstration of incorrect UTF-8 calculation of string length
     * done in Zend_Search_Lucene_Analysis_TokenFilter_ShortWords
     *
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testZendNormalizeUtf8()
    {
        // \xC5\xBC is Latin Small Letter Z with dot above (U+017C) in UTF-8
        $token = new Zend_Search_Lucene_Analysis_Token("a\xC5\xBC", 0, 3);
        $filter = new Zend_Search_Lucene_Analysis_TokenFilter_ShortWords(3);

        // 2-letter UTF-8 string should be filtered out, but since ZF ShortWords
        // filter doesn't support multi-byte encodings, it isn't
        $this->assertEquals(null, $filter->normalize($token));
    }

    public function testNormalizeUtf8()
    {
        $token = new Zend_Search_Lucene_Analysis_Token("a\xC5\xBC", 0, 3);
        $filter = new Zefram_Search_Lucene_Analysis_TokenFilter_ShortWords(array(
            'minLength' => 3,
            'encoding' => 'UTF-8',
        ));

        $this->assertEquals(null, $filter->normalize($token));
    }
}
