<?php

/**
 * Token filter that removes stop words.
 *
 * Constructor allows stop words to be provided either as data array
 * ('data' option), or loaded from file (if 'file' option is provided).
 *
 * When loading stop words from file UTF-8 BOM is skipped.
 *
 * @category   Zefram
 * @package    Zefram_Search_Lucene
 * @subpackage Analysis
 * @author     xemlock
 * @version    2015-05-04
 */
class Zefram_Search_Lucene_Analysis_TokenFilter_StopWords extends Zefram_Search_Lucene_Analysis_TokenFilter
{
    /**
     * Encoding of filter input, stop words will use this encoding
     * @var string
     */
    protected $_encoding;

    /**
     * Stop words stored as keys
     * @var array
     */
    protected $_stopSet = array();

    /**
     * Comment start character in stop words file
     * @var string
     */
    protected $_commentChar = '#';

    /**
     * Constructor.
     *
     * Supported options:
     *    encoding
     *    data
     *    file
     *    commentChar
     *
     * Moreover, all values corresponding to integer keys in options array will
     * be treated as stop words.
     *
     * @param  array $options
     */
    public function __construct(array $options = null)
    {
        // To maintain compatibility with the original StopWords filter, all
        // values at integer keys in input array will be treated as stop words.
        $stopWords = null; // lazy array initialization
        if ($options) {
            foreach ($options as $key => $value) {
                if (is_int($key)) {
                    $stopWords[] = $value;
                    unset($options[$key]);
                }
            }
        }

        parent::__construct($options);

        if ($stopWords) {
            $this->addFromArray($stopWords);
        }

        if (isset($options['file'])) {
            $this->loadFromFile($options['file'], $this->_commentChar);
        }
        if (isset($options['data'])) {
            $this->addFromArray($options['data']);
        }
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
     * @return Zefram_Search_Lucene_Analysis_TokenFilter_StopWords
     */
    public function setEncoding($encoding)
    {
        $encoding = trim($encoding);

        if (!strcasecmp($encoding, 'UTF-8') || !strcasecmp($encoding, 'UTF8')) {
            $encoding = 'UTF-8';
        }

        if ($encoding && $this->_encoding && $this->_encoding !== $encoding) {
            $stopSet = array();
            foreach ($this->_stopSet as $key => $value) {
                $encodedKey = iconv($this->_encoding, $encoding, $key);
                $stopSet[$encodedKey] = $value;
            }
            $this->_stopSet = $stopSet;
        }

        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * @param  string $commentChar
     * @return Zefram_Search_Lucene_Analysis_TokenFilter_StopWords
     */
    public function setCommentChar($commentChar)
    {
        $this->_commentChar = (string) $commentChar;
        return $this;
    }

    /**
     * Adds stop words from array.
     *
     * @param  array $data
     * @return Zefram_Search_Lucene_Analysis_TokenFilter_StopWords
     */
    public function addFromArray(array $data)
    {
        $data = array_map('trim', $data);

        if ($this->_encoding && $this->_encoding !== 'UTF-8') {
            foreach ($data as $key => $value) {
                $data[$key] = iconv('UTF-8', $this->_encoding, $value);
            }
        }

        $this->_stopSet = array_merge($this->_stopSet, array_flip($data));
        return $this;
    }

    /**
     * Adds stop words from file.
     *
     * File must be in UTF-8 encoding (may optionally contain BOM), at most one word in each line.
     * Comments start with commentChar, and can occur anywhere in line.
     *
     * You can call this method one or more times. New stop words are always added to current set.
     *
     * @param  string $file
     * @param  string $commentChar
     * @return Zefram_Search_Lucene_Analysis_TokenFilter_StopWords
     * @throws Zend_Search_Lucene_Exception
     */
    public function loadFromFile($file, $commentChar = null)
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new Zend_Search_Lucene_Exception("Invalid file path '$file'");
        }
        $fh = @fopen($file, 'r');
        if (!$fh) {
            throw new Zend_Search_Lucene_Exception("Cannot open file '$file'");
        }
        if ($commentChar === null) {
            $commentChar = $this->_commentChar;
        }
        while (false !== ($buffer = fgets($fh))) {
            // skip UTF-8 BOM
            if (!strncmp($buffer, "\xEF\xBB\xBF", 3)) {
                $buffer = substr($buffer, 3);
            }
            if (($pos = strpos($buffer, $commentChar)) !== false) {
                $buffer = substr($buffer, 0, $pos);
            }
            $buffer = trim($buffer);
            if (strlen($buffer) > 0) {
                if ($this->_encoding && $this->_encoding !== 'UTF-8') {
                    $buffer = iconv('UTF-8', $this->_encoding, $buffer);
                }
                $this->_stopSet[$buffer] = 1;
            }
        }
        if (!fclose($fh)) {
            throw new Zend_Search_Lucene_Exception("Cannot close file '$file'");
        }
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
        if (isset($this->_stopSet[$srcToken->getTermText()])) {
            return null;
        }
        return $srcToken;
    }
}
