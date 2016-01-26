<?php

/**
 * The main problem of Zend_Form_ view helpers is that they don't support
 * instances of Zend_Form_Element, and as such are cumbersome to deal with.
 * This helper accepts Zend_Form_Element as the first parameter.
 */
class Zefram_View_Helper_FormSelect extends Zend_View_Helper_FormSelect
{
    /**
     * Generates 'select' list of options.
     *
     * @access public
     *
     * @param string|array|Zend_Form_Element $name If a string, the element
     * name.  If an array, all other parameters are ignored, and the array
     * elements are extracted in place of added parameters.  If a
     * Zend_Form_Element, all parameters, such as value, attributes or
     * options, are extracted in place of other parameters.
     *
     * @param mixed $value The option value to mark as 'selected'; if an
     * array, will mark all values in the array as 'selected' (used for
     * multiple-select elements).
     *
     * @param array|string $attribs Attributes added to the 'select' tag.
     * the optional 'optionClasses' attribute is used to add a class to
     * the options within the select (associative array linking the option
     * value to the desired class)
     *
     * @param array $options An array of key-value pairs where the array
     * key is the radio value, and the array value is the radio text.
     *
     * @param string $listsep When disabled, use this list separator string
     * between list values.
     *
     * @return string The select tag and options XHTML.
     */
    public function formSelect($name, $value = null, $attribs = null,
                               $options = null, $listsep = "<br />\n")
    {
        if ($name instanceof Zend_Form_Element) {
            $element = $name;

            $name = $element->getFullyQualifiedName();
            $value = $element->getValue();
            $attribs = $element->getAttribs();

            if (method_exists($element, 'getMultiOptions')) {
                $options = $element->getMultiOptions();
            }
        }

        return parent::formSelect($name, $value, $attribs, $options, $listsep);
    }
}
