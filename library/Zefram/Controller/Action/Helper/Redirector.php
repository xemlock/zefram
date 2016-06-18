<?php

$__ZEFRAM_ERROR_REPORTING = error_reporting(error_reporting() & ~E_STRICT);

class Zefram_Controller_Action_Helper_Redirector extends Zend_Controller_Action_Helper_Redirector
{
    /**
     * If $name parameter is an array, treat it as $urlOptions for
     * compatibility with original Redirector helper.
     *
     * @param string|array $name
     * @param string|array $urlOptions
     * @param bool $reset
     * @param bool $encode
     * @return void
     */
    public function gotoRoute($name, $urlOptions = null, $reset = false, $encode = true)
    {
        if (is_array($name)) {
            list($name, $urlOptions) = array($urlOptions, $name);
        }

        parent::gotoRoute((array) $urlOptions, $name, $reset, $encode);
    }

    /**
     * @param string|array $name
     * @param string|array $urlOptions
     * @param bool $reset
     * @return void
     */
    public function gotoRouteAndExit($name, $urlOptions = null, $reset = false)
    {
        if (is_array($name)) {
            list($name, $urlOptions) = array($urlOptions, $name);
        }

        parent::gotoRouteAndExit((array) $urlOptions, $name, $reset);
    }

    /**
     * Performs even smarter detection if baseUrl should be prepended.
     *
     * @param string $url
     * @param array  $options
     * @return void
     */
    public function setGotoUrl($url, array $options = null)
    {
        $request = $this->getRequest();
        if ($request instanceof Zend_Controller_Request_Http) {
            $base = $request->getBaseUrl();
            $options['prependBase'] = strncmp($url, $base, strlen($base));
        }
        parent::setGotoUrl($url, (array) $options);
    }
}

error_reporting($__ZEFRAM_ERROR_REPORTING);
