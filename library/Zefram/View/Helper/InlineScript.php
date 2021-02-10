<?php

/**
 * Helper for setting and retrieving script elements for inclusion in HTML body
 * section
 */
class Zefram_View_Helper_InlineScript extends Zefram_View_Helper_HeadScript
{
    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'Zend_View_Helper_InlineScript';

    /**
     * Return InlineScript helper object
     *
     * Optionally it allows specifying a script or script file to include.
     *
     * @param  string $mode Script or file
     * @param  string $spec Script/url
     * @param  string $placement Append, prepend, or set
     * @param  array $attrs Array of script attributes
     * @param  string $type Script type and/or array of script attributes
     * @return $this
     */
    public function inlineScript($mode = Zend_View_Helper_HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
    {
        return $this->headScript($mode, $spec, $placement, $attrs, $type);
    }
}
