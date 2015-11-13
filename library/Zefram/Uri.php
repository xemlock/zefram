<?php

/**
 * Generic URI handler
 *
 * @package   Zefram_Uri
 * @uses      Zend_Uri
 * @author    xemlock
 */
abstract class Zefram_Uri extends Zend_Uri
{
    public static function factory($uri = 'http', $className = null)
    {
        // It turns out that Zend_Uri_Http can handle any correct URL, not
        // only those having http(s) scheme, so use it as default class.
        if ($className === null) {
            $className = 'Zend_Uri_Http';
        }
        return parent::factory($uri, $className);
    }
}
