<?php

/**
 * Base class for text analyzers offering the same functionality as all
 * analyzers shipped with Zend_Search_Lucene, but without code duplication.
 *
 * Additionally filters can be provided as a definition array, similar to
 * how elements can be provided to `Zend_Form`.
 *
 * @category   Zefram
 * @package    Zefram_Search
 * @author     xemlock
 * @version    2015-05-04
 */
class Zefram_Search_Lucene_Analysis_Analyzer extends Zend_Search_Lucene_Analysis_Analyzer
{
    /**
     * @var Zend_Loader_PluginLoader_Interface
     */
    protected $_pluginLoader;

    /**
     * @var string
     */
    protected $_encoding;

    /**
     * @var bool
     */
    protected $_tokenizeNumbers;

    /**
     * @var array
     */
    protected $_filters = array();

    /**
     * Current char position in a stream
     *
     * @var integer
     */
    private $_position;

    /**
     * Current binary position in a stream
     *
     * @var integer
     */
    private $_bytePosition;

    /**
     * @param  array $options
     */
    public function __construct(array $options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * @param  array $options
     * @return Zefram_Search_Lucene_Analysis_Analyzer
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
        return $this;
    }

    /**
     * @param  boolean $flag
     * @return Zefram_Search_Lucene_Analysis_Analyzer
     */
    public function setTokenizeNumbers($flag)
    {
        $this->_tokenizeNumbers = (bool) $flag;
        return $this;
    }

    /**
     * @param  string $encoding
     * @return bool
     * @throws Zend_Search_Lucene_Exception
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $this->_checkEncoding($encoding);
        return true;
    }

    /**
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader()
    {
        if (null === $this->_pluginLoader) {
            $this->_pluginLoader = new Zend_Loader_PluginLoader(array(
                'Zend_Search_Lucene_Analysis_TokenFilter_'   => 'Zend/Search/Lucene/Analysis/TokenFilter/',
                'Zefram_Search_Lucene_Analysis_TokenFilter_' => 'Zefram/Search/Lucene/Analysis/TokenFilter/',
            ));
        }
        return $this->_pluginLoader;
    }

    /**
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @return Zefram_Search_Lucene_Analysis_Analyzer
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader)
    {
        $this->_pluginLoader = $loader;
        return $this;
    }

    /**
     * Retrieve all filters
     *
     * @return Zend_Search_Lucene_Analysis_TokenFilter[]
     */
    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * Add filters to analyzer
     *
     * Each filter can be given in one of the following forms:
     * <ul>
     *   <li>Zend_Search_Lucene_Analysis_TokenFilter $filter
     *   <li>string $name
     *   <li>array('filter' => string $name, 'options' => array $options)
     *   <li>array(string $name, array $options)
     * </ul>
     *
     * @param  array $filters
     * @return Zefram_Search_Lucene_Analysis_Analyzer
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $filterSpec) {
            if ($filterSpec instanceof Zend_Search_Lucene_Analysis_TokenFilter) {
                $this->addFilter($filterSpec);
                continue;
            }

            if (is_array($filterSpec)) {
                $options = array();

                if (isset($filterSpec['filter'])) {
                    $filter = $filterSpec['filter'];
                    if (isset($filterSpec['options'])) {
                        $options = $filterSpec['options'];
                    }
                    $this->addFilter($filter, (array) $options);
                } else {
                    $filter = null;
                    $argc = count($filterSpec);
                    switch ($argc) {
                        case 0:
                            break;

                        /** @noinspection PhpMissingBreakStatementInspection */
                        case (1 <= $argc):
                            $filter = array_shift($filterSpec);

                        /** @noinspection PhpMissingBreakStatementInspection */
                        case (2 <= $argc):
                            $options = array_shift($filterSpec);

                        default:
                            $this->addFilter($filter, (array) $options);
                    }
                }
            } else {
                $this->addFilter($filterSpec);
            }
        }
        return $this;
    }

    /**
     * Set multiple filters, overwriting previous filters
     *
     * @param  array $filters
     * @return Zefram_Search_Lucene_Analysis_Analyzer
     */
    public function setFilters(array $filters)
    {
        $this->_filters = array();
        return $this->addFilters($filters);
    }

    /**
     * Add filter to analyzer
     *
     * @param  Zend_Search_Lucene_Analysis_TokenFilter|string $filter
     * @param  array $options
     * @return Zefram_Search_Lucene_Analysis_Analyzer
     */
    public function addFilter($filter, array $options = null)
    {
        if (!$filter instanceof Zend_Search_Lucene_Analysis_TokenFilter) {
            $class = $this->getPluginLoader()->load($filter);
            if (empty($options)) {
                $filter = new $class();
            } else {
                $ref = new ReflectionClass($class);
                if ($ref->hasMethod('__construct')) {
                    $numeric = false;
                    $keys    = array_keys($options);
                    foreach ($keys as $key) {
                        if (is_numeric($key)) {
                            $numeric = true;
                            break;
                        }
                    }

                    if ($numeric) {
                        $filter = $ref->newInstanceArgs($options);
                    } else {
                        $filter = $ref->newInstance($options);
                    }
                } else {
                    $filter = $ref->newInstance();
                }
            }
        }
        $this->_filters[] = $filter;
        return $this;
    }

    protected function _checkEncoding($encoding)
    {
        $encoding = trim($encoding);

        if (!strcasecmp($encoding, 'utf8') || !strcasecmp($encoding, 'utf-8')) {
            if (@preg_match('/\pL/u', 'a') != 1) {
                // PCRE unicode support is turned off
                throw new Zend_Search_Lucene_Exception('Analyzer requires PCRE unicode support to be enabled.');
            }
            $encoding = 'UTF-8';
        }

        return $encoding;
    }

    /**
     * Reset token stream
     *
     * @return void
     */
    public function reset()
    {
        $this->_position     = 0;
        $this->_bytePosition = 0;

        // convert non-ASCII encoding into UTF-8
        if ($this->_encoding && $this->_encoding !== 'UTF-8') {
            $this->_input = iconv($this->_encoding, 'UTF-8', $this->_input);
            $this->setEncoding('UTF-8');
        }
    }

    protected function _getTokenRegex()
    {
        if ($this->_encoding === 'UTF-8') {
            if ($this->_tokenizeNumbers) {
                $regex = '/[\p{L}]+/u';
            } else {
                $regex = '/[\p{L}\p{N}]+/u';
            }
        } else {
            if ($this->_tokenizeNumbers) {
                $regex = '/[a-zA-Z0-9]+/';
            } else {
                $regex = '/[a-zA-Z]+/';
            }
        }
        return $regex;
    }

    /*
     * Get next token.
     *
     * Returns null at the end of stream
     *
     * @return Zend_Search_Lucene_Analysis_Token|null
     */
    public function nextToken()
    {
        if ($this->_input === null) {
            return null;
        }

        $regex = $this->_getTokenRegex();

        do {
            if (!preg_match($regex, $this->_input, $match, PREG_OFFSET_CAPTURE, $this->_bytePosition)) {
                // It covers both cases a) there are no matches (preg_match(...) === 0)
                // b) error occured (preg_match(...) === FALSE)
                return null;
            }

            // matched string
            $matchedWord = $match[0][0];

            // binary position of the matched word in the input stream
            $binStartPos = $match[0][1];

            // character position of the matched word in the input stream
            $startPos = $this->_position +
                        iconv_strlen(substr($this->_input,
                                            $this->_bytePosition,
                                            $binStartPos - $this->_bytePosition),
                                     'UTF-8');
            // character postion of the end of matched word in the input stream
            $endPos = $startPos + iconv_strlen($matchedWord, 'UTF-8');

            $this->_bytePosition = $binStartPos + strlen($matchedWord);
            $this->_position     = $endPos;

            $token = $this->normalize(new Zend_Search_Lucene_Analysis_Token($matchedWord, $startPos, $endPos));
        } while ($token === null); // try again if token is skipped

        return $token;
    }

    public function normalize(Zend_Search_Lucene_Analysis_Token $token)
    {
        foreach ($this->_filters as $filter) {
            $token = $filter->normalize($token);

            // resulting token can be null if the filter removes it
            if ($token === null) {
                return null;
            }
        }

        return $token;
    }
}
