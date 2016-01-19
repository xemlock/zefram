<?php

class Zefram_Form2 extends Zend_Form
{
    protected static $_defaultPrefixPaths = array();

    /**
     * @var array
     */
    protected $_data;

    public function __construct($options = null)
    {
        // configure loaders before handling options, so that any prefix paths
        // provided in options will have higher priority than the below ones
        $this->addPrefixPath('Zefram_Form_Element_',   'Zefram/Form/Element/',   self::ELEMENT);
        $this->addPrefixPath('Zefram_Form_Decorator_', 'Zefram/Form/Decorator/', self::DECORATOR);

        $this->addElementPrefixPath('Zefram_Validate_', 'Zefram/Validate/', Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Zefram_Filter_',   'Zefram/Filter/', Zend_Form_Element::FILTER);

        // unified options handling
        if (is_object($options) && method_exists($options, 'toArray')) {
            $options = $options->toArray();
        }

        parent::__construct((array) $options);
    }

    public function setData(array $data = null)
    {
        $this->_data = $data;
        return $this;
    }

    public function isValid($data = null)
    {
        if ($data === null) {
            $data = (array) $this->_data;
        }
        return parent::isValid($data);
    }

    public function isValidPartial(array $data = null)
    {
        if ($data === null) {
            $data = (array) $this->_data;
        }
        return parent::isValidPartial($data);
    }

    public function addSubForm(Zend_Form $form, $name = null, $order = null)
    {
        // handle order if passed as the second param
        if (is_int($name)) {
            $order = $name;
            $name = null;
        }
        if ($name === null) {
            $name = $form->getName();
        }
        return parent::addSubForm($form, $name, $order);
    }

    public function getPluginLoader($type = null)
    {
        $type = strtoupper($type);

        if (!isset($this->_loaders[$type])) {
            $loader = parent::getPluginLoader($type);

            // add default prefix paths after creating loader
            if (isset(self::$_defaultPrefixPaths[$type])) {
                foreach (self::$_defaultPrefixPaths[$type] as $prefix => $path) {
                    $loader->addPrefixPath($prefix, $path);
                }
            }

            return $loader;
        }

        return $this->_loaders[$type];
    }

    /**
     * Add default prefix path for given type
     *
     * @param  string $prefix
     * @param  string $path
     * @param  string $type 'decorator' or 'element'
     * @throws Zend_Form_Exception on invalid type
     */
    public static function addDefaultPrefixPath($prefix, $path, $type)
    {
        $type = strtoupper($type);

        switch ($type) {
            case self::DECORATOR:
            case self::ELEMENT:
                self::$_defaultPrefixPaths[$type][$prefix] = $path;
                break;

            default:
                throw new Zend_Form_Exception(sprintf('Invalid type "%s" provided to addDefaultPrefixPath()', $type));
        }
    }
}
