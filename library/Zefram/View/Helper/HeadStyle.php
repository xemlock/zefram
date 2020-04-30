<?php

/**
 * Added support for 'unescape' attribute, that will prevent style contents
 * from being wrapped in HTML comments.
 *
 * Default value of 'media' attribute has been set to 'all', as 'screen' is
 * not a sensible default.
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
        $noescape = null;
        if (isset($item->attributes['noescape'])) {
            $noescape = $item->attributes['noescape'];
            unset($item->attributes['noescape']);
        }

        $string = parent::itemToString($item, $indent);

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
}
