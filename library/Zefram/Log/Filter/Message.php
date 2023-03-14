<?php

/**
 * Added support for inverse matching, i.e. accepting messages that don't
 * match the provided regular expression.
 */
class Zefram_Log_Filter_Message extends Zend_Log_Filter_Message
{
    /**
     * @var bool
     */
    protected $_invert;

    public function __construct($regexp, $invert = false)
    {
        parent::__construct($regexp);
        $this->_invert = (bool) $invert;
    }

    public function accept($event)
    {
        $accept = parent::accept($event);
        return $this->_invert ? !$accept : $accept;
    }

    public static function factory($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(array(
            'regexp' => null,
            'invert' => false,
        ), $config);

        return new self($config['regexp'], $config['invert']);
    }
}
