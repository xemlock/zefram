<?php

/**
 * Zend_Measure package is inconvenient to work with, because of the following:
 *
 * - It's missing a value parser, which can autodetect unit of the value - so
 *   you have to do the unit parsing prior to instantiating a measure object
 * - It's missing a factory class, which makes it unsuitable for one-line use,
 *   as instantiation and formatting cannot be done in a single expression in
 *   PHP versions prior to 5.4
 *
 * This implementation aims to solve all of these issues.
 *
 * @category Zefram
 * @package  Zefram_Measure
 * @author   xemlock
 */
abstract class Zefram_Measure
{
    /**
     * @var Zend_Loader_PluginLoader_Interface
     */
    protected static $_pluginLoader;

    /**
     * @return Zend_Loader_PluginLoader_Interface
     */
    public static function getPluginLoader()
    {
        if (null === self::$_pluginLoader) {
            self::$_pluginLoader = new Zend_Loader_PluginLoader(array(
                'Zend_Measure_'   => 'Zend/Measure/',
                'Zefram_Measure_' => 'Zefram/Measure/',
            ));
        }
        return self::$_pluginLoader;
    }

    /**
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @return void
     */
    public static function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader)
    {
        self::$_pluginLoader = $loader;
    }

    /**
     * Factory for Zend_Measure_Abstract classes
     *
     * If value is provided as string, and the type is unspecified, this method
     * tries to infer the type by examining provided value's suffix.
     *
     * @param  string      $measure Name of measure class
     * @param  mixed       $value   Value as string, integer, real or float
     * @param  int         $type    OPTIONAL a measure type e.g. Zend_Measure_Length::METER
     * @param  Zend_Locale $locale  OPTIONAL a Zend_Locale Type
     * @return Zend_Measure_Abstract
     * @throws Zend_Measure_Exception
     */
    public static function factory($measure, $value, $type = null, $locale = null)
    {
        $measureClass = self::getPluginLoader()->load($measure);

        if ($type === null && is_string($value)) {
            /** @var Zend_Measure_Abstract $instance */
            $instance = new $measureClass(null, null, $locale);

            // this exposes a design flaw in Zend_Measure - you cannot get
            // conversion list without instatiating a measure object
            foreach ($instance->getConversionList() as $t => $spec) {
                if (!is_array($spec)) {
                    continue;
                }

                list(, $unit) = $spec;
                if ($unit === substr($value, -strlen($unit))) {
                    $type = $t;
                    $value = trim(substr($value, 0, -strlen($unit)));
                    break;
                }
            }
        }

        return new $measureClass($value, $type, $locale);
    }
}
