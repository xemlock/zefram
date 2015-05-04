<?php

/**
 * Token filter that transforms the token according to the Porter stemming
 * algorithm.
 *
 * Note: the input to the stemming filter must already be in lower case, so
 * you will need to use LowerCaseFilter or LowerCaseTokenizer farther down
 * the Tokenizer chain in order for this to work properly.
 *
 * @category   Zefram
 * @package    Zefram_Search_Lucene
 * @subpackage Analysis
 * @author     xemlock
 * @version    2015-05-04
 */
class Zefram_Search_Lucene_Analysis_TokenFilter_PorterStem extends Zefram_Search_Lucene_Analysis_TokenFilter
{
    public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken)
    {
        return Zefram_Search_PorterStemmer::Stem($srcToken);
    }
}
