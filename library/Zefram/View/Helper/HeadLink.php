<?php

/**
 * Stylesheets have 'all' media by default, which is more sensible than 'screen'.
 * Additionally, if NULL is passed as stylesheet media then this default value
 * is used as well.
 */
class Zefram_View_Helper_HeadLink extends Zend_View_Helper_HeadLink
{
    public function createDataStylesheet(array $args)
    {
        if (empty($args[1])) {
            $args[1] = 'all';
        }
        return parent::createDataStylesheet($args);
    }
}
