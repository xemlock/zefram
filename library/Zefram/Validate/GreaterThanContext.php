<?php

/**
 * GreaterThanContext is an implementation of GreaterThan validator with
 * challenge value taken from context.
 *
 * @version 2015-04-04
 * @author xemlock
 */
class Zefram_Validate_GreaterThanContext extends Zend_Validate_Abstract
{
    const NO_CONTEXT            = 'noContext';
    const NOT_GREATER           = 'notGreaterThan';
    const NOT_GREATER_INCLUSIVE = 'notGreaterThanInclusive';

    /**
     * Error message templates
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NO_CONTEXT            => 'No context value was provided to match against',
        self::NOT_GREATER           => "The input is not greater than '%min%'",
        self::NOT_GREATER_INCLUSIVE => "The input is not greater or equal than '%min%'",
    );

    /**
     * Additional variables available for error messages
     * @var array
     */
    protected $_messageVariables = array(
        'min' => '_min',
    );

    /**
     * Key pointing at the value from the context array, that the value
     * provided to the {@link isValid()} method will be validated against
     *
     * @var int|string
     */
    protected $_contextKey;

    /**
     * @var bool
     */
    protected $_inclusive;

    /**
     * Minimum value provided in context to compare against
     *
     * This will be automatically set in {@link isValid()}.
     *
     * @var mixed
     */
    protected $_min;

    /**
     * Configure the validator
     *
     * @param  mixed $options
     * @return void
     * @throws Zend_Validate_Exception
     */
    public function __construct($options = null)
    {
        if (is_object($options) && method_exists($options, 'toArray')) {
            $options = $options->toArray();
        }

        if (!is_array($options)) {
            $options = func_get_args();
            $temp['contextKey'] = array_shift($options);

            if (!empty($options)) {
                $temp['inclusive'] = array_shift($options);
            }

            $options = $temp;
        }

        if (empty($options['contextKey'])) {
            throw new Zend_Validate_Exception("Missing option 'contextKey'");
        }

        if (!array_key_exists('inclusive', $options)) {
            $options['inclusive'] = false;
        }

        $this->setContextKey($options['contextKey']);
        $this->setInclusive($options['inclusive']);
    }

    /**
     * Sets context key option
     *
     * @param  int|string $contextKey
     * @return Zefram_Validate_GreaterThanContext
     */
    public function setContextKey($contextKey)
    {
        if (!is_scalar($contextKey)) {
            throw new Zend_Validate_Exception('Context key must be a scalar value');
        }
        $this->_contextKey = $contextKey;
        return $this;
    }

    /**
     * Returns context key option
     *
     * @return mixed
     */
    public function getContextKey()
    {
        return $this->_contextKey;
    }

    /**
     * Sets the inclusive option
     *
     * @param  bool $inclusive
     * @return Zefram_Validate_GreaterThanContext
     */
    public function setInclusive($inclusive)
    {
        $this->_inclusive = (bool) $inclusive;
        return $this;
    }

    /**
     * Returns inclusive option
     *
     * @return bool
     */
    public function getInclusive()
    {
        return $this->_inclusive;
    }

    /**
     * Returns true if and only if $value is greater than provided
     * context value
     *
     * @param  mixed $value
     * @param  array $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->_setValue($value);

        if (!isset($context[$this->_contextKey])) {
            $this->_error(self::NO_CONTEXT);
            return false;
        }

        $min = $this->_min = $context[$this->_contextKey];

        if ($this->getInclusive()) {
            if ($value < $min) {
                $this->_error(self::NOT_GREATER_INCLUSIVE);
                return false;
            }
        } else {
            if ($value <= $min) {
                $this->_error(self::NOT_GREATER);
                return false;
            }
        }

        return true;
    }
}
