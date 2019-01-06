<?php

/**
 * @category   Zefram
 * @package    Zefram_View
 * @subpackage Helper
 * @author     xemlock
 * @version    2014-07-05
 */
class Zefram_View_Helper_FileSize extends Zend_View_Helper_Abstract
{
    /**
     * @param  int $bytes
     * @param  int $precision OPTIONAL  Number of decimal digits to round to
     * @param  string $mode OPTIONAL    One of {@link Zefram_Filter_FileSize}::MODE_ constants
     * @return string
     */
    public function fileSize($bytes, $precision = null, $mode = null)
    {
        try {
            $locale = $this->view->translate()->getLocale();
        } catch (Zend_Locale_Exception $e) {
            $locale = null;
        }
        return Zefram_Filter_FileSize::filterStatic($bytes, compact('precision', 'mode', 'locale'));
    }
}
