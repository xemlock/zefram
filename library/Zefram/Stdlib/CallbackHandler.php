<?php

/**
 * CallbackHandler with preset arguments that will be passed to registered
 * callback upon its invocation. This allows a more functional style
 * programming.
 *
 * @package Zefram_Stdlib
 * @uses    Zend_Stdlib_CallbackHandler
 * @version 2014-12-11
 * @author  xemlock
 */
class Zefram_Stdlib_CallbackHandler extends Zend_Stdlib_CallbackHandler
{
    /**
     * @var array
     */
    protected $_args = array();

    /**
     * Constructor.
     *
     * @param callable $callback
     * @param array $metadata
     * @param array $args
     */
    public function __construct($callback, array $metadata = array(), array $args = array())
    {
        // if args param is empty and metadata is an array with
        // consecutive integer keys, it will be treated as args
        if (empty($args)
            && count($metadata)
            && array_keys($metadata) === range(0, count($metadata) - 1)
        ) {
            $args = $metadata;
            $metadata = array();
        }

        // if args are explicitly given, they overwrite any args present in
        // the handler instance, otherwise take args from callback
        if ($callback instanceof Zefram_Stdlib_CallbackHandler
            && empty($args)
        ) {
            $args = $callback->getArgs();
        }

        // copy constructor, use internal callback value
        if ($callback instanceof Zend_Stdlib_CallbackHandler) {
            if (empty($metadata)) {
                $metadata = $callback->getMetadata();
            }
            $callback = $callback->getCallback();
        }

        // call_user_func() in PHP versions prior to 5.2.2 can't handle callbacks given
        // as class::method string
        // PHP prior to 7.0 can't handle function calls via variable name (i.e. $func()),
        // if function name is given as class::method
        // To overcome this function name is split into object/class and method parts
        if (is_string($callback) && (false !== strpos($callback, '::'))) {
            $callback = explode('::', $callback, 2);
        }

        // Since PHP 5.3.0 objects are callable if __invoke() method is
        // implemented.
        // Use __invoke method when it is detected and given callback object is
        // not callable (PHP 5.0.0 - 5.2.x)
        if (is_object($callback)
            && !is_callable($callback)
            && method_exists($callback, '__invoke')
        ) {
            $callback = array($callback, '__invoke');
        }

        parent::__construct($callback, $metadata);

        $this->_args = array_values($args);
    }

    /**
     * Retrieve callback arguments.
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->_args;
    }

    /**
     * Invoke registered callback.
     *
     * @param  array $args OPTIONAL
     * @return mixed
     */
    public function call(array $args = array())
    {
        for ($i = count($this->_args) - 1; $i >= 0; --$i) {
            array_unshift($args, $this->_args[$i]);
        }
        return parent::call($args);
    }

    /**
     * Invoke registered callback with explicitly listed arguments.
     *
     * @param  mixed $param OPTIONAL
     * @param  mixed $param,... OPTIONAL
     * @return mixed
     */
    public function invoke($param = null)
    {
        // arguments not provided won't be included, from PHP doc:
        // func_get_args() returns a copy of the passed arguments only, and
        // does not account for default (non-passed) arguments.
        // http://php.net/manual/en/function.func-get-args.php
        $args = func_get_args();
        return $this->call($args);
    }
}
