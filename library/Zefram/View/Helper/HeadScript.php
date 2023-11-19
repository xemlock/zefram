<?php

/**
 * Original HeadScript view helper fails at properly inserting new lines
 * when escaping script contents:
 *
 * Original impl of toString() when escaping is on:
 * <script>
 *     //<!--
 *     CONTENT    //-->
 * </script>
 *
 * Desired output:
 * <script>
 *     //<!--
 *     CONTENT
 *     //-->
 * </script>
 *
 * When escaping is off (noescape attribute present and equal FALSE):
 * <script>
 *     SCRIPT</script>
 *
 * Desired output:
 * <script>
 *     SCRIPT
 * </script>
 *
 * Moreover in original impl input value whitespaces are not ignored, which
 * can lead to messy indents (contrary to HeadStyle which has proper indent
 * and newline handling).
 *
 * Changes overview:
 * - Feature: Empty scripts (no content or empty 'src' attribute) are not rendered
 * - Feature: SCRIPT closing tag is properly escaped if present in the source
 * - Feature: In HTML5 doctype script source is not escaped by default
 * - Fix: If no type is given or the type is empty, a default 'text/javascript' is used
 *
 * @property Zend_View|Zend_View_Abstract|Zend_View_Interface $view
 * @method $this setIndent(string $indent)
 * @method $this setSeparator(string $separator)
 * @method string getIndent()
 * @method string getSeparator()
 * @method string getWhitespace(int|string $indent)
 */
class Zefram_View_Helper_HeadScript extends Zend_View_Helper_HeadScript
{
    /**
     * https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types#JavaScript_types
     * @var string[]
     */
    protected $_javaScriptTypes = array(
        'application/javascript',
        'application/ecmascript',
        'application/x-ecmascript',
        'application/x-javascript',
        'text/javascript',
        'text/ecmascript',
        'text/javascript1.0',
        'text/javascript1.1',
        'text/javascript1.2',
        'text/javascript1.3',
        'text/javascript1.4',
        'text/javascript1.5',
        'text/jscript',
        'text/livescript',
        'text/x-ecmascript',
        'text/x-javascript',
    );

    /**
     * Create script HTML
     *
     * @param  stdClass $item
     * @param  string $indent
     * @param  string $escapeStart
     * @param  string $escapeEnd
     * @return string
     */
    public function itemToString($item, $indent, $escapeStart, $escapeEnd)
    {
        $item = (object) $item;
        $source = isset($item->source) ? $item->source : null;

        $item->source = trim((string) $source);
        if (stripos($item->source, '</script') !== false) {
            $item->source = preg_replace('/<\/(script[\s>])/i', '<\\/$1', $item->source);
        }

        if (empty($item->type)) {
            $item->type = 'text/javascript';
        }

        if (empty($item->source) && empty($item->attributes['src'])
            && in_array(strtolower($item->type), $this->_javaScriptTypes, true)
        ) {
            return '';
        }

        if (strlen($item->source)) {
            $item->source .= PHP_EOL;
        }

        $noescape = isset($item->attributes['noescape']) ? $item->attributes['noescape'] : null;
        if ($noescape === null && $this->view instanceof Zend_View_Abstract && $this->view->doctype()->isHtml5()) {
            $item->attributes['noescape'] = true;
        }

        $string = parent::itemToString($item, $indent, $escapeStart, $escapeEnd);
        $item->source = $source;
        $item->attributes['noescape'] = $noescape;

        return $string;
    }

    public function toString($indent = null)
    {
        $indent = (null !== $indent)
            ? $this->getWhitespace($indent)
            : $this->getIndent();

        $string = parent::toString($indent);
        $string = preg_replace('/<\/script>\s+<script/', '</script>' . PHP_EOL . $indent . '<script', $string);

        return $string;
    }
}
