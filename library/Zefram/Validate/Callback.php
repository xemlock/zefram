<?php

class Zefram_Validate_Callback extends Zend_Validate_Callback
{
    public function __construct($callback = null)
    {
        parent::__construct($callback);

        // if no options are explicitly given use this validator instance,
        // so that it can be referenced from within the callback
        if (is_array($callback) && !array_key_exists('options', $callback)) {
            $this->setOptions(array($this));
        }
    }
}
