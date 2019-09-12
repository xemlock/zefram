<?php

/**
 * Stream writer with added support for setting logfile permissions.
 *
 * @category   Zefram
 * @package    Zefram_Log
 * @subpackage Writer
 */
class Zefram_Log_Writer_Stream extends Zend_Log_Writer_Stream
{
    /**
     * Constructor
     *
     * @param array|string|resource $streamOrUrl Stream or URL to open as a stream
     * @param string|null $mode Mode, only applicable if a URL is given
     * @param int|null $filePermissions Permissions value, only applicable if a filename is given;
     *     when $streamOrUrl is an array of options, use the 'chmod' key to specify this.
     * @return void
     * @throws Zend_Log_Exception
     */
    public function __construct($streamOrUrl, $mode = null, $filePermissions = null)
    {
        if (is_array($streamOrUrl)) {
            $mode            = isset($streamOrUrl['mode']) ? $streamOrUrl['mode'] : $mode;
            $filePermissions = isset($streamOrUrl['chmod']) ? $streamOrUrl['chmod'] : $filePermissions;
            $streamOrUrl     = isset($streamOrUrl['stream']) ? $streamOrUrl['stream'] : null;
        }

        if (is_string($streamOrUrl)) {
            if (isset($filePermissions) && !file_exists($streamOrUrl) && is_writable(dirname($streamOrUrl))) {
                touch($streamOrUrl);
                chmod($streamOrUrl, $filePermissions);
            }
        }

        parent::__construct($streamOrUrl, $mode);
    }

    /**
     * Create a new instance of Zefram_Log_Writer_Stream
     *
     * @param  array|Zend_Config $config
     * @return Zefram_Log_Writer_Stream
     */
    static public function factory($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(array(
            'stream' => null,
            'mode'   => null,
            'chmod'  => null,
        ), $config);

        $streamOrUrl = isset($config['url']) ? $config['url'] : $config['stream'];

        return new self(
            $streamOrUrl,
            $config['mode'],
            $config['chmod']
        );
    }
}
