<?php

class Zefram_Form2 extends Zend_Form
{
    /**
     * Default prefix paths for plugin loader
     * @var array
     */
    protected static $_defaultPrefixPaths = array();

    /**
     * Prefix paths to use when creating elements
     * @var array
     */
    protected $_elementPrefixPaths = array(
        array(
            'prefix' => 'Zefram_Filter_',
            'path'   => 'Zefram/Filter/',
            'type'   => Zend_Form_Element::FILTER,
        ),
        array(
            'prefix' => 'Zefram_Validate_',
            'path'   => 'Zefram/Validate/',
            'type'   => Zend_Form_Element::VALIDATE,
        ),
    );

    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->_init();
    }

    /**
     * Initialize form (used by extending classes)
     *
     * Contrary to {@link init()} this method is not designed to be called
     * externally, and as such it provides more clean initialization logic
     * executed as a final step of object initialization.
     *
     * @return void
     */
    protected function _init()
    {}

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

            switch ($type) {
                case self::DECORATOR:
                    $prefixSegment = 'Zefram_Form_Decorator';
                    $pathSegment   = 'Zefram/Form/Decorator';
                    break;
                case self::ELEMENT:
                    $prefixSegment = 'Zefram_Form_Element';
                    $pathSegment   = 'Zefram/Form/Element';
                    break;
                default:
                    // make lint happy
                    throw new Zend_Form_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
            }
            $loader->addPrefixPath($prefixSegment, $pathSegment);

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
