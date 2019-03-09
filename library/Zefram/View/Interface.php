<?php

/**
 * This interface exists only for PHPDoc-based autocompletion purposes.
 *
 * @method string baseUrl(string $file = null)
 * @method string serverUrl(string|boolean $requestUri = null)
 * @method string url(string|array $name = null, string|array $urlOptions = null, bool $reset = false, bool $encode = true)
 * @method string translate(string $messageid = null, mixed ...$values)
 * @method Zend_View_Helper_HeadLink headLink()
 * @method Zend_View_Helper_HeadMeta headMeta()
 * @method Zend_View_Helper_HeadScript headScript()
 * @method Zend_View_Helper_HeadStyle headStyle()
 * @method Zend_View_Helper_HeadTitle headTitle($title = null, $setType = null)
 * @method Zefram_View_Helper_Gravatar gravatar(string $email = null, array $options = null, array $attribs = null)
 */
interface Zefram_View_Interface extends Zend_View_Interface
{}