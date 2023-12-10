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

function getZendFrameworkVersion() {
    // In some community maintained distributions Zend_Version class may not exist,
    // Zend_Config class however is widely used by the ZF internals.
    // Additionally version in Zend_Version::VERSION can be out of sync from the
    // version in composer.json.
    $anchorClass = new ReflectionClass('Zend_Config');
    $packageName = null;
    $packageVersion = class_exists('Zend_Version') ? Zend_Version::VERSION : null;

    $dir = dirname($anchorClass->getFileName());
    while (true) {
        if (file_exists($composerJson = $dir . '/composer.json')) {
            $package = json_decode(file_get_contents($composerJson));
            $version = \Composer\InstalledVersions::getPrettyVersion($package->name);
            if ($version) {
                $packageName = $package->name;
                $packageVersion = $version;
                break;
            }
        }
        if (($parent = realpath($dir . '/..')) === $dir) {
            break;
        }
        $dir = $parent;
    }

    return $packageVersion
        . ($packageName && $packageName !== 'zendframework/zendframework1' ? " ({$packageName})" : '');
};

echo "Zend Framework version: ", getZendFrameworkVersion(), "\n";
echo "PHP version:            ", PHP_VERSION, "\n";
echo "PHP memory limit:       ", ini_get('memory_limit'), "\n";
echo "\n";
