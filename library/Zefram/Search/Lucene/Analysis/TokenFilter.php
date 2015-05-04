<?php

/**
 * Base class for token filters.
 *
 * @category   Zefram
 * @package    Zefram_Search
 * @author     xemlock
 * @version    2015-05-04
 */
abstract class Zefram_Search_Lucene_Analysis_TokenFilter extends Zend_Search_Lucene_Analysis_TokenFilter
{
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
     * @return Zend_Search_Lucene_Analysis_TokenFilter
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
}
