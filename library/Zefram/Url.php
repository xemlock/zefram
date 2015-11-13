<?php

/**
 * @deprecated
 */
class Zefram_Url extends Zend_Uri
{
    /**
     * URL factory.
     *
     * @param  string $uri
     * @return Zend_Uri_Http
     * @deprecated Use Zefram_Uri::factory() instead
     */
    public static function fromString($uri)
    {
        return Zefram_Uri::factory($uri);
    }
}
