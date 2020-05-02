<?php

/**
 * Added support for 'unescape' attribute, that will prevent style contents
 * from being wrapped in HTML comments.
 *
 * Default value of 'media' attribute has been set to 'all', as 'screen' is
 * not a sensible default.
 *
 * Empty styles are not rendered.
 *
 * String <code></style></code> if present in the content is properly escaped.
 *
 * @property Zend_View|Zend_View_Abstract|Zend_View_Interface $view
 * @method $this setIndent(string $indent)
 * @method $this setSeparator(string $separator)
 * @method string getIndent()
 * @method string getSeparator()
 * @method string getWhitespace(int|string $indent)
 */
class Zefram_View_Helper_HeadStyle extends Zend_View_Helper_HeadStyle
{
    public function createData($content, array $attributes)
    {
        if (empty($attributes['media'])) {
            $attributes['media'] = 'all';
        }
        return parent::createData($content, $attributes);
    }

    public function itemToString(stdClass $item, $indent)
    {
        $content = isset($item->content) ? $item->content : null;

        if (stripos($content, '</style') !== false) {
            $content = preg_replace('/<\/(style[\s>])/i', '<\\/$1', $content);
        }
        $content = trim($content);

        if (!strlen($content)) {
            return '';
        }

        $noescape = null;
        if (isset($item->attributes['noescape'])) {
            $noescape = $item->attributes['noescape'];
            unset($item->attributes['noescape']);
        }

        $originalContent = $item->content;
        $item->content = $content;
        $string = parent::itemToString($item, $indent);
        $item->content = $originalContent;

        if ($noescape !== null) {
            if ($noescape) {
                $escapeStart = $indent . '<!--'. PHP_EOL;
                $escapeEnd = $indent . '-->'. PHP_EOL;

                $string = str_replace('>' . PHP_EOL . $escapeStart, '>' . PHP_EOL, $string);
                $string = str_replace($escapeEnd . '</style>', '</style>', $string);
            }
            $item->attributes['noescape'] = $noescape;
        }

        return $string;
    }

    public function toString($indent = null)
    {
        $indent = (null !== $indent)
            ? $this->getWhitespace($indent)
            : $this->getIndent();

        $string = parent::toString($indent);
        // normalize newlines, in case there are empty or invalid styles in the container
        $string = preg_replace('/<\/style>\s+<style/', '</style>' . PHP_EOL . $indent . '<style', $string);
        return $string;
    }
}
