<?php

/**
 * Provides more encapsulated Lucene index implementation.
 *
 * @category   Zefram
 * @package    Zefram_Search
 * @author     xemlock
 */
class Zefram_Search_Lucene extends Zend_Search_Lucene
{
    /**
     * @var Zend_Search_Lucene_Analysis_Analyzer
     */
    protected $_analyzer;

    /**
     * @var ReflectionClass
     */
    protected $_lucene;

    /**
     * @param  string $directory
     * @param  bool $create
     */
    public function __construct($directory = null, $create = false)
    {
        $this->_analyzer = Zend_Search_Lucene_Analysis_Analyzer::getDefault();
        $this->_lucene = new ReflectionClass('Zend_Search_Lucene');

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
        return $this->_runWithAnalyzer($this->_lucene->getMethod('find'), $query);
    }

    /**
     * {@inheritdoc}
     *
     * @param  Zend_Search_Lucene_Document $document
     */
    public function addDocument(Zend_Search_Lucene_Document $document)
    {
        return $this->_runWithAnalyzer($this->_lucene->getMethod('addDocument'), $document);
    }

    /**
     * @internal
     * @param  string|ReflectionMethod $method
     * @return mixed
     */
    protected function _runWithAnalyzer($method)
    {
        $prevAnalyzer = null;
        $throw = null;

        if ($this->_analyzer) {
            $prevAnalyzer = Zend_Search_Lucene_Analysis_Analyzer::getDefault();
            Zend_Search_Lucene_Analysis_Analyzer::setDefault($this->_analyzer);
        }

        $args = func_get_args();
        array_shift($args);

        try {
            if ($method instanceof ReflectionMethod) {
                $result = $method->invokeArgs($this, $args);
            } else {
                $result = call_user_func_array(array($this, $method), $args);
            }
        } catch (Exception $e) {
            $throw = $e;
        }

        if ($prevAnalyzer) {
            Zend_Search_Lucene_Analysis_Analyzer::setDefault($prevAnalyzer);
        }

        if ($throw) {
            throw $throw;
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
