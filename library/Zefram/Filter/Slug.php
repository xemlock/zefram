<?php

/**
 * Filter for creating SEO friendly slugs.
 *
 * @version 2014-07-31
 * @author  xemlock
 */ 
class Zefram_Filter_Slug implements Zend_Filter_Interface
{
    /**
     * Transliterator used for slug generation
     * @var Zefram_Filter_Translit
     */
    protected $_transliterator;

    /**
     * Returns the transliterator used for slug generation.
     *
     * @return Zefram_Filter_Translit
     */
    public function getTransliterator()
    {
        return $this->_transliterator;
    }

    /**
     * Sets the transliterator used for slug generation.
     *
     * @param Zefram_Filter_Translit $transliterator
     * @return Zefram_Filter_Slug
     */
    public function setTransliterator(Zefram_Filter_Translit $transliterator = null)
    {
        $this->_transliterator = $transliterator;
        return $this;
    }

    /**
     * Generate slug from given string.
     *
     * @param string $string Input string to be filtered
     * @return string A generated slug
     */
    public function filter($string)
    {
        return self::filterStatic($string, array('transliterator' => $this->getTransliterator()));
    }

    /**
     * @var Zefram_Filter_Translit
     */
    protected static $_defaultTransliterator;

    /**
     * Generate slug from given string without requiring separate instantiation of the filter object.
     *
     * @param string $string Input string to be filtered
     * @param array $options Array of options controlling filter behaviour
     * @return string A generated slug
     * @throws Zend_Filter_Exception When provided transliterator is not an instance of Zend_Filter_Interface
     */
    public static function filterStatic($string, array $options = null)
    {
        if (isset($options['transliterator'])) {
            $transliterator = $options['transliterator'];
            if (!$transliterator instanceof Zend_Filter_Interface) {
                throw new Zend_Filter_Exception('Transliterator must be an instance of Zend_Filter_Interface');
            }
        } else {
            $transliterator = self::getDefaultTransliterator();
        }

        $string = $transliterator->filter($string);

        $string = preg_replace(array(
            '/[^-0-9a-z]/i',
            '/-+/',
        ), '-', $string);
        $string = trim($string, '-');
        $string = strtolower($string);

        return $string;
    }

    /**
     * Returns the default transliterator.
     *
     * @return Zefram_Filter_Translit
     */
    public static function getDefaultTransliterator()
    {
        if (self::$_defaultTransliterator === null) {
            self::$_defaultTransliterator = new Zefram_Filter_Translit();
        }
        return self::$_defaultTransliterator;
    }
}
