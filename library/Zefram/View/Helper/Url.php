<?php

$__ZEFRAM_ERROR_REPORTING = error_reporting(error_reporting() & ~E_STRICT);

/**
 * @author xemlock
 * @version 2014-05-15
 */
class Zefram_View_Helper_Url extends Zend_View_Helper_Url
{
    /**
     * Assembles a URL based on a given route.
     *
     * @param string|array $routeName
     * @param string|array $urlParams
     * @param bool $reset
     * @param bool $encode
     * @return string
     */
    public function url($routeName, $urlParams = null, $reset = false, $encode = true)
    {
        if (is_array($routeName)) {
            list($urlParams, $routeName) = array($routeName, $urlParams);
        }
        return parent::url((array) $urlParams, $routeName, $reset, $encode);
    }
}

error_reporting($__ZEFRAM_ERROR_REPORTING);
