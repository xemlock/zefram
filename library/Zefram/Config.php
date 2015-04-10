<?php

class Zefram_Config extends Zend_Config
{
    /**
     * Creates a config object based on the path of a given file.
     *
     * @param  string     $file      file to process
     * @param  string     $section   section to process
     * @param  array|bool $options
     * @return Zend_Config
     * @throws Zend_Config_Exception When file cannot be loaded
     * @throws Zend_Config_Exception When section cannot be found in file contents
     */
    public static function factory($file, $section = null, $options = false)
    {
        $suffix = pathinfo($file, PATHINFO_EXTENSION);
        $suffix = ($suffix === 'dist')
                ? pathinfo(basename($file, ".$suffix"), PATHINFO_EXTENSION)
                : $suffix;

        switch (strtolower($suffix)) {
            case 'ini':
                $config = new Zefram_Config_Ini($file, $section, $options);
                break;

            case 'xml':
                $config = new Zend_Config_Xml($file, $section, $options);
                break;

            case 'json':
                $config = new Zend_Config_Json($file, $section, $options);
                break;

            case 'yaml':
            case 'yml':
                $config = new Zend_Config_Yaml($file, $section, $options);
                break;

            case 'php':
            case 'inc':
                $config = include $file;
                if (!is_array($config)) {
                    throw new Zend_Config_Exception(
                        'Invalid configuration file provided; PHP file does not return array value'
                    );
                }
                return $config;

            default:
                throw new Zend_Config_Exception(sprintf(
                    "Invalid configuration file provided; unknown config type '%s'",
                    $suffix
                ));
        }

        return $config;
    }
}
