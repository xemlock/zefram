<?php

/**
 * Lower case token filter capable of handling multiple text encodings.
 *
 * @category   Zefram
 * @package    Zefram_Search_Lucene
 * @subpackage Analysis
 * @author     xemlock
 * @version    2015-05-04
 */
class Zefram_Search_Lucene_Analysis_TokenFilter_LowerCase extends Zefram_Search_Lucene_Analysis_TokenFilter
{
    /**
     * @var string
     */
    protected $_encoding;

    /**
     * Get filter encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set filter encoding
     *
     * @param  string $encoding
     * @return Zefram_Search_Lucene_Analysis_TokenFilter_LowerCase
     * @throws Zend_Search_Lucene_Exception
     */
    public function setEncoding($encoding)
    {
        $encoding = trim($encoding);

        if (!strcasecmp($encoding, 'UTF-8') || !strcasecmp($encoding, 'UTF8')) {
            $encoding = 'UTF-8';
        }

        if ($encoding && !function_exists('mb_strtolower')) {
            throw new Zend_Search_Lucene_Exception('Filter requires mbstring extension to be enabled.');
        }

        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Normalize Token
     *
     * @param Zend_Search_Lucene_Analysis_Token $srcToken
     * @return Zend_Search_Lucene_Analysis_Token
     */
    public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken)
    {
        $text = $this->_toLowerCase($srcToken->getTermText());
        $srcToken->setTermText($text);
        return $srcToken;
    }

    /**
     * Lowercase string.
     *
     * @param  string $text
     * @return string
     */
    protected function _toLowerCase($text)
    {
        if (empty($this->_encoding)) {
            $text = strtolower($text);
        } else {
            $text = mb_strtolower($text, $this->_encoding);
        }
        return $text;
    }
}
