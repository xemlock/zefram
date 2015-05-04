<?php

/**
 * Provides more encapsulated Lucene index implementation.
 *
 * @category   Zefram
 * @package    Zefram_Search
 * @author     xemlock
 * @version    2015-05-04
 */
class Zefram_Search_Lucene extends Zend_Search_Lucene
{
    /**
     * @var Zend_Search_Lucene_Analysis_Analyzer
     */
    protected $_analyzer;

    /**
     * @param  string $directory
     * @param  bool $create
     */
    public function __construct($directory = null, $create = false)
    {
        $this->_analyzer = Zend_Search_Lucene_Analysis_Analyzer::getDefault();

        parent::__construct($directory, $create);
    }

    /**
     * Returns the Zend_Search_Lucene_Analysis_Analyzer instance for this index.
     *
     * @return Zend_Search_Lucene_Analysis_Analyzer
     */
    public function getAnalyzer()
    {
        return $this->_analyzer;
    }

    /**
     * {@inheritdoc}
     *
     * @param  Zend_Search_Lucene_Search_QueryParser|string $query
     * @return Zend_Search_Lucene_Search_QueryHit[]
     * @throws Zend_Search_Lucene_Exception
     */
    public function find($query)
    {
        // calling parent method using call_user_func via 'parent::method'
        // works since PHP 5.1.2
        return $this->_runWithAnalyzer('parent::find', $query);
    }

    /**
     * {@inheritdoc}
     *
     * @param  Zend_Search_Lucene_Document $document
     */
    public function addDocument(Zend_Search_Lucene_Document $document)
    {
        return $this->_runWithAnalyzer('parent::addDocument', $document);
    }

    /**
     * @internal
     * @param  string $method
     * @return mixed
     */
    protected function _runWithAnalyzer($method)
    {
        $analyzer = $this->_analyzer;
        $prevAnalyzer = null;

        if ($analyzer) {
            $prevAnalyzer = Zend_Search_Lucene_Analysis_Analyzer::getDefault();
            Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzer);
        }

        $args = func_get_args();
        array_shift($args);

        $result = call_user_func_array(array($this, $method), $args);

        if ($analyzer) {
            Zend_Search_Lucene_Analysis_Analyzer::setDefault($prevAnalyzer);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed $directory
     * @return Zend_Search_Lucene_Interface
     */
    public static function open($directory)
    {
        return new Zend_Search_Lucene_Proxy(new Zefram_Search_Lucene($directory, false));
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed $directory
     * @return Zend_Search_Lucene_Interface
     */
    public static function create($directory)
    {
        return new Zend_Search_Lucene_Proxy(new Zefram_Search_Lucene($directory, true));
    }
}
