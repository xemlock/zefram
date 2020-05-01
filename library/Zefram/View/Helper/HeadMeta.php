<?php

/**
 * Fixed support for <code><meta property= ... /></code> - they are valid in all doctypes,
 * not only in RDFa.
 */
class Zefram_View_Helper_HeadMeta extends Zend_View_Helper_HeadMeta
{
    protected function _isValid($item)
    {
        if ($item instanceof stdClass && isset($item->type) && $item->type === 'property') {
            return true;
        }
        return parent::_isValid($item);
    }

    public function itemToString(stdClass $item)
    {
        $string = parent::itemToString($item);
        $string = str_replace(' >', '>', $string); // Remove extra space before end bracket
        return $string;
    }
}
