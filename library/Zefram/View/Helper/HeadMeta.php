<?php

/**
 * Fixed support for <code><meta property= ... /></code> - they are valid in
 * (X)HTML5 doctype, not only in RDFa.
 *
 * @property Zend_View|Zend_View_Abstract|Zend_View_Interface $view
 * @method $this setIndent(string $indent)
 * @method $this setSeparator(string $separator)
 * @method string getIndent()
 * @method string getSeparator()
 * @method string getWhitespace(int|string $indent)
 */
class Zefram_View_Helper_HeadMeta extends Zend_View_Helper_HeadMeta
{
    protected function _isValid($item)
    {
        if ($item instanceof stdClass && isset($item->type) && $item->type === 'property'
            && ($this->view instanceof Zend_View_Abstract && $this->view->doctype()->isHtml5())
        ) {
            return true;
        }
        return parent::_isValid($item);
    }

    public function itemToString(stdClass $item)
    {
        $string = parent::itemToString($item);

        // Unify XHTML tag endings
        if ($this->view instanceof Zend_View_Abstract && $this->view->doctype()->isXhtml()) {
            $string = str_replace('" />', '"/>', $string);
        }

        $string = str_replace(' >', '>', $string); // Remove extra space before end bracket
        return $string;
    }
}
