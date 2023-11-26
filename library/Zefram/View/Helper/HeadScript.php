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
 * Additionally original implementation doesn't support HTML5 sttributes by
 * default, and requires a call to setAllowArbitraryAttributes(TRUE) to
 * allow them.
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
     * https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types#textjavascript
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
        $source = isset($item->source) ? trim((string) $item->source) : '';

        if (empty($source) && empty($item->attributes['src']) &&
            (empty($item->type) || in_array(strtolower($item->type), $this->_javaScriptTypes, true))
        ) {
            return '';
        }

        // Ensure there is no unescaped script closing tag
        if (stripos($source, '</script') !== false) {
            $source = preg_replace('/<\/(script[\s>])/i', '<\\/$1', $source);
        }

        $isHtml5 = $this->view instanceof Zend_View_Abstract && $this->view->doctype()->isHtml5();

        $noEscape = isset($item->attributes['noescape'])
            ? filter_var($item->attributes['noescape'], FILTER_VALIDATE_BOOLEAN)
            : $isHtml5;

        $attrString = '';
        if (!empty($item->attributes)) {
            foreach ($item->attributes as $key => $value) {
                if ((!$this->arbitraryAttributesAllowed() && !in_array($key, $this->_optionalAttributes))
                    || in_array($key, array('conditional', 'noescape')))
                {
                    continue;
                }
                if ($key === 'defer' && !$isHtml5) {
                    $value = 'defer';
                }
                if ($isHtml5 && empty($value)) {
                    $attrString .= sprintf(' %s', $key);
                } else {
                    $attrString .= sprintf(' %s="%s"', $key, $this->_autoEscape ? $this->_escape($value) : $value);
                }
            }
        }

        $addScriptEscape = !$noEscape;

        $type = $this->_autoEscape ? $this->_escape($item->type) : $item->type;
        if ($isHtml5 && (empty($type) || $type === 'text/javascript')) {
            $html  = '<script' . $attrString . '>';
        } else {
            $html  = '<script type="' . $type . '"' . $attrString . '>';
        }

        if (!empty($source)) {
            $html .= PHP_EOL;

            if ($addScriptEscape) {
                $html .= $indent . '    ' . $escapeStart . PHP_EOL;
            }

            $html .= $indent . '    ' . $source . PHP_EOL;

            if ($addScriptEscape) {
                $html .= $indent . '    ' . $escapeEnd . PHP_EOL;
            }

            $html .= $indent;
        }
        $html .= '</script>';

        if (isset($item->attributes['conditional'])
            && !empty($item->attributes['conditional'])
            && is_string($item->attributes['conditional']))
        {
            // inner wrap with comment end and start if !IE
            if (str_replace(' ', '', $item->attributes['conditional']) === '!IE') {
                $html = '<!-->' . $html . '<!--';
            }
            $html = $indent . '<!--[if ' . $item->attributes['conditional'] . ']>' . $html . '<![endif]-->';
        } else {
            $html = $indent . $html;
        }

        return $html;
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
