<?php

/**
 * Provides a way of defining sets of default decorators for each element type.
 * This greatly improves cross-application reusability of form elements.
 */
abstract class Zefram_Form_Element extends Zend_Form_Element
{
    protected static $_defaultDecorators = array(
        'Zend_Form_Element' => array(
            'ViewHelper',
            'Errors',
            array('Description', array('tag' => 'p', 'class' => 'description')),
            array('HtmlTag', array('tag' => 'dd')),
            array('Label', array('tag' => 'dt')),
        ),
        'Zend_Form_Element_Captcha' => array(
            'Errors',
            array('Description', array('tag' => 'p', 'class' => 'description')),
            array('HtmlTag', array('tag' => 'dd')),
            array('Label', array('tag' => 'dt')),
        ),
        'Zend_Form_Element_Hidden' => array(
            'ViewHelper',
        ),
        'Zend_Form_Element_Image' => array(
            'Tooltip',
            'Image',
            'Errors',
            array('HtmlTag', array('tag' => 'dd')),
            array('Label', array('tag' => 'dt')),
        ),
        'Zend_Form_Element_Submit' => array(
            'Tooltip',
            'ViewHelper',
            'DtDdWrapper',
        ),
    );

    /**
     * @param Zend_Form_Element $element
     * @param mixed $htmlTagId
     * @internal
     */
    public static function _loadDefaultDecorators(Zend_Form_Element $element, $htmlTagId = null)
    {
        if ($element->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = array();
        foreach (self::_findDefaultDecorators($element) as $decorator) {
            if ($decorator instanceof Zend_Form_Decorator_Interface) {
                $decorators[] = clone $decorator;
            } else {
                $decorators[] = $decorator;
            }
        }

        $element->setDecorators($decorators);

        if (($decorator = $element->getDecorator('HtmlTag')) !== false) {
            /** @var Zend_Form_Decorator_HtmlTag $decorator */
            if ($htmlTagId) {
                $htmlTagId = is_callable($htmlTagId) ? array('callback' => $htmlTagId) : $htmlTagId;
            } else {
                $htmlTagId = array('callback' => array(get_class($element), 'resolveElementId'));
            }
            $decorator->setOption('id', $htmlTagId);
        }
    }

    /**
     * @param Zend_Form_Element $element
     * @return array[]
     * @throws ReflectionException
     */
    protected static function _findDefaultDecorators(Zend_Form_Element $element)
    {
        $refClass = new ReflectionClass($element);

        while ($refClass) {
            if (isset(self::$_defaultDecorators[$refClass->getName()])) {
                return self::$_defaultDecorators[$refClass->getName()];
            }
            $refClass = $refClass->getParentClass();
        }

        return self::$_defaultDecorators['Zend_Form_Element'];
    }

    /**
     * @param string|array $type
     * @param array $decorators OPTIONAL
     * @param string $type OPTIONAL
     */
    public static function setDefaultDecorators($type, array $decorators = array())
    {
        if (is_array($type)) {
            $decorators = $type;
            $type = 'Zend_Form_Element';
        }
        self::$_defaultDecorators[$type] = $decorators;
    }
}
