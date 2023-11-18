<?php

require_once __DIR__ . '/../vendor/autoload.php';

// PHPUnit >= 6.0 compatibility
if (!class_exists('PHPUnit_Framework_TestSuite') && class_exists('PHPUnit\Framework\TestSuite')) {
    /** @noinspection PhpIgnoredClassAliasDeclaration */
    class_alias('PHPUnit\Framework\TestSuite', 'PHPUnit_Framework_TestSuite');
}

if (!class_exists('PHPUnit_Framework_TestCase') && class_exists('PHPUnit\Framework\TestCase')) {
    /** @noinspection PhpIgnoredClassAliasDeclaration */
    class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
}

if (!class_exists('PHPUnit_Framework_Error_Error') && class_exists('PHPUnit\Framework\Error\Error')) {
    /** @noinspection PhpIgnoredClassAliasDeclaration */
    class_alias('PHPUnit\Framework\Error\Error', 'PHPUnit_Framework_Error_Error');
}

if (!class_exists('PHPUnit_Framework_AssertionFailedError') && class_exists('PHPUnit\Framework\AssertionFailedError')) {
    /** @noinspection PhpIgnoredClassAliasDeclaration */
    class_alias('PHPUnit\Framework\AssertionFailedError', 'PHPUnit_Framework_AssertionFailedError');
}

echo "Zend Framework version: ", Zend_Version::VERSION, "\n";
echo "PHP version:            ", PHP_VERSION, "\n";
echo "PHP memory limit:       ", ini_get('memory_limit'), "\n";
echo "\n";
