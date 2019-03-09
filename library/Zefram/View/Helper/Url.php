<?php

/**
 * Because of strict Standards: Zend_View_Helper_Url declaration requires that first param is array.
 *
 * The reason for this helper is that giving route as url(params, name) is inconvenient,
 * especially when params are not always required. So instead of writing url(array(), 'name')
 * by using this helper one can just type url('name').
 *
 * For backwards compatibility both order of arguments is supported (name, options) and (options, names).
 *
 * @package    Zefram_View
 * @subpackage Helper
 * @author     xemlock
 */
class Zefram_View_Helper_Url extends Zend_View_Helper_Abstract
{
    /**
     * Generates an URL based on a given route
     *
     * @param string|array $name        If string - the name of a route to use, if array - options passed to the assemble method of the Route object. If null it will use the current Route
     * @param string|array $urlOptions  If first parameter is a string then this parameter is treated as options. Otherwise it will be treated as route name
     * @param bool $reset               Whether or not to reset the route defaults with those provided
     * @param bool $encode              Whether or not to encode URL parts on output
     * @return string
     */
    public function url($name = null, $urlOptions = null, $reset = false, $encode = true)
    {
        if (is_array($name)) {
            list($urlOptions, $name) = array($name, $urlOptions);
        }

        if ($urlOptions === null) {
            $urlOptions = array();
        }

        // helper implementation has not changed since ZF 1.6.0

        $router = Zend_Controller_Front::getInstance()->getRouter();
        return $router->assemble($urlOptions, $name, $reset, $encode);
    }
}
