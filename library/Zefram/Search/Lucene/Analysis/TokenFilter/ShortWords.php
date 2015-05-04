<?php

/**
 * Token filter that removes short words.
 *
 * Zend implementation improperly calculates length for multibute (UTF-8) strings.
 *
 * @category   Zefram
 * @package    Zefram_Search_Lucene
 * @subpackage Analysis
 * @author     xemlock
 * @version    2015-05-04
 */
class Zefram_Search_Lucene_Analysis_TokenFilter_ShortWords extends Zefram_Search_Lucene_Analysis_TokenFilter
{
    /**
     * @var string
     */
    protected $_encoding;

    /**
     * Minimum allowed term length
     * @var integer
     */
    protected $_minLength = 2;

    /**
     * @param  array|int $options
     */
    public function __construct($options = null)
    {
        if (is_int($options)) {
            $options = array('minLength' => $options);
        }
        parent::__construct($options);
    }

    /**
     * Get minimum term length
     *
     * @return int
     */
    public function getMinLength()
    {
        return $this->_minLength;
    }

    /**
     * Set minimum term length
     *
     * @param  int $minLength
     * @return Zefram_Search_Lucene_Analysis_TokenFilter_ShortWords
     * @throws Zend_Search_Lucene_Exception
     */
    public function setMinLength($minLength)
    {
        $minLength = (int) $minLength;
        if ($minLength < 0) {
            throw new Zend_Search_Lucene_Exception('Minimum length must be greater or equal zero');
        }
        $this->_minLength = $minLength;
        return $this;
    }

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
     * @return Zefram_Search_Lucene_Analysis_TokenFilter_ShortWords
     * @throws Zend_Search_Lucene_Exception
     */
    public function setEncoding($encoding)
    {
        $encoding = trim($encoding);

        if (!strcasecmp($encoding, 'UTF-8') || !strcasecmp($encoding, 'UTF8')) {
            $encoding = 'UTF-8';
        }

        if ($encoding && !function_exists('mb_strlen')) {
            throw new Zend_Search_Lucene_Exception('Filter requires mbstring extension to be enabled.');
        }

        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Normalize Token or remove it (if null is returned)
     *
     * @param Zend_Search_Lucene_Analysis_Token $srcToken
     * @return Zend_Search_Lucene_Analysis_Token|null
     */
    public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken)
    {
        if (empty($this->_encoding)) {
            $length = strlen($srcToken->getTermText());
        } else {
            $length = mb_strlen($srcToken->getTermText(), $this->_encoding);
        }

        if ($length < $this->_minLength) {
            return null;
        } else {
            return $srcToken;
        }
    }
}
