<?php

/**
 * This is an upgrade for Zend_Mail_Transport_File that properly validates
 * provided path and callback.
 *
 * The original implementation of setOptions() silently ignored invalid path
 * or callback provided. This could result in invalid (or unexpected)
 * directory/callback to be used when writing file.
 *
 * @uses Zend_Mail
 */
class Zefram_Mail_Transport_File extends Zend_Mail_Transport_File
{
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
    }

    /**
     * Sets callback for generating file names
     *
     * @param callable $callback
     * @return Zefram_Mail_Transport_File
     * @throws Zend_Mail_Transport_Exception on invalid callback
     */
    public function setCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new Zend_Mail_Transport_Exception('Invalid callback provided');
        }
        $this->_callback = $callback;
        return $this;
    }

    /**
     * Sets path to directory where files are written
     *
     * @param $path
     * @return Zefram_Mail_Transport_File
     * @throws Zend_Mail_Transport_Exception on nonexistent or not writable directory
     */
    public function setPath($path)
    {
        if (!is_dir($path) || !is_writable($path)) {
            throw new Zend_Mail_Transport_Exception(sprintf(
                'Target directory "%s" does not exist or is not writable',
                $path
            ));
        }
        $this->_path = $path;
        return $this;
    }
}
