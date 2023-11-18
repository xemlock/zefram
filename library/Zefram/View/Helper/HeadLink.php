<?php

/**
 * Stylesheets have 'all' media by default, as per HTML5 spec.
 * Additionally, if NULL is passed as stylesheet's media attribute, then this
 * default value is used as well (and not a NULL one).
 *
 * Fixed rendering when there are invalid items in the container.
 *
 * @property Zend_View|Zend_View_Abstract|Zend_View_Interface $view
 * @method $this appendStylesheet($href, $media = 'all', $conditionalStylesheet = false, array $extras = array())
 * @method $this offsetSetStylesheet($index, $href, $media = 'all', $conditionalStylesheet = false, array $extras = array())
 * @method $this prependStylesheet($href, $media = 'all', $conditionalStylesheet = false, array $extras = array())
 * @method $this setStylesheet($href, $media = 'all', $conditionalStylesheet = false, array $extras = array())
 * @method $this setIndent(string $indent)
 * @method $this setSeparator(string $separator)
 * @method string getIndent()
 * @method string getSeparator()
 * @method string getWhitespace(int|string $indent)
 */
class Zefram_View_Helper_HeadLink extends Zend_View_Helper_HeadLink
{
    public function createDataStylesheet(array $args)
    {
        // Make sure 'extras' argument is always provided and is an array, to fix:
        // PHP Notice: compact(): Undefined variable: extras in Zend/View/Helper/HeadLink.php on line 404
        // Since PHP 7.3 compact() issues an E_NOTICE level error if a given string refers
        // to an unset variable. Formerly, such strings have been silently skipped.

        // The order of arguments is as follows: href, media, conditional, extras
        $args = array_values($args) + array(null, null, false, array());

        // In HTML 4.01, the default value is 'screen',
        // https://www.w3.org/TR/html4/present/styles.html#adef-media
        // In HTML5, the default value has been changed to 'all',
        // https://html.spec.whatwg.org/multipage/semantics.html#processing-the-media-attribute
        if (empty($args[1])) {
            $args[1] = 'all';
        }

        if (!is_array($args[3])) {
            $args[3] = array();
        }

        return parent::createDataStylesheet($args);
    }

    public function itemToString(stdClass $item)
    {
        $string = parent::itemToString($item);
        $string = str_replace(' >', '>', $string);  // Remove extra space before end bracket
        return $string;
    }

    public function toString($indent = null)
    {
        $indent = (null !== $indent)
            ? $this->getWhitespace($indent)
            : $this->getIndent();

        $string = parent::toString($indent);

        // Unify XHTML tag endings, to match other helpers
        if ($this->view instanceof Zend_View_Abstract && $this->view->doctype()->isXhtml()) {
            $string = str_replace('" />', '"/>', $string);
        }

        // Normalize newlines, in case there are invalid items in the container
        $string = preg_replace('/>\s+<link/', '>' . PHP_EOL . $indent . '<link', $string);
        return $string;
    }
}
