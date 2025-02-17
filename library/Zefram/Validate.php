<?php

/**
 * Validator chain.
 *
 * Extension to Zend_Validate allowing validators to be passed the
 * same way as they are to {@see Zend_Form_Element::addValidator()}.
 *
 * Validator can be used in Zend_Form_Element in conjunction with
 * allowEmpty=FALSE, required=FALSE and NotEmptyIf validator.
 *
 * isValid() call can accept context parameter that will be passed
 * to validators.
 *
 * @category Zefram
 * @package  Zefram_Validate
 * @author   xemlock
 * @version  2015-06-27 / 2014-12-13 / 2013-10-18
 * @uses     Zend_Validate
 * @uses     Zend_Loader
 *
 * 2015-11-26: Strict Standards conformance
 * 2015-06-27: added context parameter to isValid()
 * 2014-12-13: added allowEmpty feature
 */
class Zefram_Validate implements Zend_Validate_Interface
{
    /**
     * Validator chain
     *
     * @var array{instance: Zend_Validate_Interface, breakChainOnFailure: bool}[]
     */
    protected $_validators = array();

    /**
     * Array of validation failure messages
     *
     * @var array
     */
    protected $_messages = array();

    /**
     * @var bool
     */
    protected $_allowEmpty = false;

    /**
     * @var bool
     */
    protected $_breakChainOnFailure = false;

    /**
     * @var Zend_Loader
     */
    protected $_pluginLoader;

    /**
     * Translation object
     * @var Zend_Translate
     */
    protected $_translator;

    /**
     * Constructor
     *
     * @param array|object $options
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            if (is_object($options) && method_exists($options, 'toArray')) {
                $options = $options->toArray();
            }

            $options = (array) $options;

            // set loader before loading any validators

            if (isset($options['pluginLoader'])) {
                $this->setPluginLoader($options['pluginLoader']);
                unset($options['pluginLoader']);
            }

            // add prefix paths before loading any validators

            if (isset($options['prefixPaths'])) {
                $this->addPrefixPaths($options['prefixPaths']);
                unset($options['prefixPaths']);
            }

            foreach ($options as $key => $value) {
                $method = 'set' . $key;
                if (method_exists($this, $method)) {
                    $this->$method($value);
                    unset($options[$key]);
                }
            }

            if (isset($options['validators'])) {
                $this->addValidators($options['validators']);

            } elseif ($options) {
                // treat any remaining options as validators, if no explicit
                // 'validators' option is provided
                $this->addValidators($options);
            }
        }
    }

    /**
     * Returns true if and only if $value passes all validations in the chain.
     *
     * Validators are run in the order in which they were added to the chain
     * (FIFO).
     *
     * Additionally, if 'allow empty' flag is set, an empty value automatically
     * passes validation.
     *
     * @param  mixed $value
     * @param  array $context OPTIONAL
     * @return bool
     */
    public function isValid($value, array $context = array())
    {
        $this->_messages = array();

        if ($this->_allowEmpty && ($value === '' || $value === null)) {
            return true;
        }

        // this is a copy-paste from parent::isValid() with context parameter
        // added to isValid() calls, as the original implementation didn't
        // support it
        $result = true;
        foreach ($this->_validators as $element) {
            /** @var Zend_Validate_Interface $validator */
            $validator = $element['instance'];
            if ($validator->isValid($value, $context)) {
                continue;
            }
            $result = false;
            $messages = $validator->getMessages();
            $this->_messages = array_merge($this->_messages, $messages);
            if ($element['breakChainOnFailure']) {
                break;
            }
        }
        return $result;
    }

    /**
     * Set 'allow empty' flag
     *
     * When the 'allow empty' flag is enabled empty values will automatically
     * pass validation.
     *
     * @param  bool $flag
     * @return $this
     */
    public function setAllowEmpty($flag)
    {
        $this->_allowEmpty = (bool) $flag;
        return $this;
    }

    /**
     * Get 'allow empty' flag
     *
     * @return bool
     */
    public function getAllowEmpty()
    {
        return $this->_allowEmpty;
    }

    /**
     * @param string|array|Zend_Validate_Interface $validator
     * @param bool $breakChainOnFailure
     * @param array $options
     * @return $this
     * @throws Zend_Validate_Exception
     */
    public function addValidator($validator, $breakChainOnFailure = false, array $options = null)
    {
        if (is_array($validator)) {
            $validator = array_slice($validator, 0, 3);
            $count = count($validator);

            switch (true) {
                /** @noinspection PhpMissingBreakStatementInspection */
                case $count >= 3:
                    $options = (array) array_pop($validator);

                /** @noinspection PhpMissingBreakStatementInspection */
                case $count >= 2:
                    $breakChainOnFailure = array_pop($validator);

                case $count >= 1:
                    $validator = array_pop($validator);
                    break;

                default:
                    throw new Zend_Validate_Exception(
                        'Validator specification if given as array, must be non-empty'
                    );
            }
        }

        if (null === $breakChainOnFailure) {
            $breakChainOnFailure = $this->_breakChainOnFailure;
        }

        if (isset($options['messages'])) {
            $messages = $options['messages'];
            unset($options['messages']);
        } else {
            $messages = null;
        }

        // TODO lazy validator loading
        if (!$validator instanceof Zend_Validate_Interface) {
            $className = $this->getPluginLoader()->load($validator);

            if (empty($options)) {
                $validator = new $className;
            } else {
                $ref = new ReflectionClass($className);
                if ($ref->hasMethod('__construct')) {
                    reset($options);
                    if (is_int(key($options))) {
                        $validator = $ref->newInstanceArgs($options);
                    } else {
                        $validator = $ref->newInstance($options);
                    }
                } else {
                    $validator = $ref->newInstance();
                }
            }
        }

        if ($messages) {
            if (is_array($messages)) {
                $validator->setMessages($messages);
            } elseif (is_string($messages)) {
                $validator->setMessage($messages);
            }
        }

        // do not index validators by class name, as it would prevent multiple
        // validators of the same class to co-exists
        $this->_validators[] = array(
            'instance' => $validator,
            'breakChainOnFailure' => (bool) $breakChainOnFailure,
        );

        return $this;
    }

    /**
     * @return Zend_Validate_Interface[]
     */
    public function getValidators()
    {
        $validators = array();
        foreach ($this->_validators as $validator) {
            $validators[] = $validator['instance'];
        }
        return $validators;
    }

    /**
     * Get single validator by name
     *
     * @param string $name
     * @return Zend_Validate_Interface|null
     */
    public function getValidator($name)
    {
        $len = strlen($name);
        foreach ($this->_validators as $key => $validator) {
            $validatorClass = get_class($validator['instance']);
            if ($len > strlen($validatorClass)) {
                continue;
            }
            // substr_compare($haystack, $needle, $offset, $length, $case_insensitive)
            if (0 === substr_compare($validatorClass, $name, -$len, $len, true)) {
                return $validator['instance'];
            }
        }
        return null;
    }

    /**
     * @return $this
     */
    public function clearValidators()
    {
        $this->_validators = array();
        return $this;
    }

    /**
     * @param  array $validators
     * @return $this
     */
    public function addValidators(array $validators)
    {
        foreach ($validators as $spec) {
            $this->addValidator($spec);
        }
        return $this;
    }

    /**
     * @param  array $validators
     * @return $this
     */
    public function setValidators(array $validators)
    {
        $this->clearValidators();
        return $this->addValidators($validators);
    }

    /**
     * Remove a single validator by name
     *
     * @return $this
     */
    public function removeValidator($name)
    {
        $len = strlen($name);
        foreach ($this->_validators as $key => $validator) {
            $validatorClass = get_class($validator['instance']);
            if ($len > strlen($validatorClass)) {
                continue;
            }
            // substr_compare($haystack, $needle, $offset, $length, $case_insensitive)
            if (0 === substr_compare($validatorClass, $name, -$len, $len, true)) {
                unset($this->_validators[$key]);
                break;
            }
        }
        return $this;
    }

    /**
     * @param  bool $breakChainOnFailure
     * @return $this
     */
    public function setBreakChainOnFailure($breakChainOnFailure)
    {
        $this->_breakChainOnFailure = (bool) $breakChainOnFailure;
        return $this;
    }

    /**
     * Set translation object
     *
     * @param  Zend_Translate|Zend_Translate_Adapter|null $translator
     * @return $this
     */
    public function setTranslator($translator = null)
    {
        foreach ($this->_validators as $element) {
            /** @var Zend_Validate_Interface $validator */
            $validator = $element['instance'];
            if (method_exists($validator, 'setTranslator')) {
                /** @var Zend_Validate_Abstract $validator */
                $validator->setTranslator($translator);
            }
        }
        $this->_translator = $translator;
        return $this;
    }

    /**
     * Return translation object
     *
     * @return Zend_Translate_Adapter|null
     */
    public function getTranslator()
    {
        return $this->_translator;
    }

    /**
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader()
    {
        if (null === $this->_pluginLoader) {
            $this->_pluginLoader = new Zend_Loader_PluginLoader(array(
                'Zend_Validate_'   => 'Zend/Validate/',
                'Zefram_Validate_' => 'Zefram/Validate/',
            ));
        }
        return $this->_pluginLoader;
    }

    /**
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @return $this
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader)
    {
        $this->_pluginLoader = $loader;
        return $this;
    }

    /**
     * @param  string $prefix
     * @param  string $path
     * @return $this
     */
    public function addPrefixPath($prefix, $path)
    {
        $this->getPluginLoader()->addPrefixPath($prefix, $path);
        return $this;
    }

    /**
     * @param  array $spec
     * @return $this
     */
    public function addPrefixPaths(array $spec)
    {
        foreach ($spec as $prefix => $path) {
            if (is_array($path)) {
                if (isset($path['prefix']) && isset($path['path'])) {
                    $this->addPrefixPath($path['prefix'], $path['path']);
                }
            } elseif (is_string($prefix)) {
                $this->addPrefixPath($prefix, $path);
            }
        }
        return $this;
    }

    /**
     * Returns array of validation failure messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * This allows method chaining after object instantiation.
     *
     * @param  array|Zend_Config $options
     * @return Zefram_Validate
     */
    public static function factory($options = null)
    {
        return new self($options);
    }

    /**
     * @deprecated
     */
    public static function create($options = null)
    {
        return self::factory($options);
    }

    /**
     * Returns the maximum allowed message length
     *
     * @return int
     */
    public static function getMessageLength()
    {
        return Zend_Validate_Abstract::getMessageLength();
    }

    /**
     * Sets the maximum allowed message length
     *
     * @param int $length
     */
    public static function setMessageLength($length = -1)
    {
        Zend_Validate_Abstract::setMessageLength($length);
    }

    /**
     * Returns the default translation object
     *
     * @return Zend_Translate_Adapter|null
     */
    public static function getDefaultTranslator()
    {
        return Zend_Validate_Abstract::getDefaultTranslator();
    }

    /**
     * Sets a default translation object for all validation objects
     *
     * @param Zend_Translate|Zend_Translate_Adapter|null $translator
     */
    public static function setDefaultTranslator($translator = null)
    {
        Zend_Validate_Abstract::setDefaultTranslator($translator);
    }
}
