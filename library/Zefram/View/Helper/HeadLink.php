<?php

/**
 * Stylesheets have 'all' media by default, which is more sensible than 'screen'.
 * Additionally, if NULL is passed as stylesheet's media attribute, then this
 * default value is used as well (and not a NULL one).
 *
 * Fixed rendering when there are invalid items in the container.
 *
 * @method $this appendStylesheet($href, $media = 'all', $conditionalStylesheet = false, array $extras = array())
 * @method $this offsetSetStylesheet($index, $href, $media = 'all', $conditionalStylesheet = false, array $extras = array())
 * @method $this prependStylesheet($href, $media = 'all', $conditionalStylesheet = false, array $extras = array())
 * @method $this setStylesheet($href, $media = 'all', $conditionalStylesheet = false, array $extras = array())
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
        // normalize newlines, in case there are invalid items in the container
        $string = preg_replace('/>\s+<link/', '>' . PHP_EOL . $indent . '<link', $string);
        return $string;
    }
}
