<?php

/**
 * Compatibility wrapper for PHPUnit test cases
 */
class TestCase extends PHPUnit_Framework_TestCase
{
    public static function assertIsFloat($actual, $message = '')
    {
        // Since PHPUnit 7.5
        if (method_exists(get_parent_class(__CLASS__), 'assertIsFloat')) {
            return parent::assertIsFloat($actual, $message);
        }
        return self::assertInternalType('float', $actual, $message);
    }

    public static function assertIsInt($actual, $message = '')
    {
        // Since PHPUnit 7.5
        if (method_exists(get_parent_class(__CLASS__), 'assertIsInt')) {
            return parent::assertIsInt($actual, $message);
        }
        return self::assertInternalType('int', $actual, $message);
    }

    public static function assertIsString($actual, $message = '')
    {
        // Since PHPUnit 7.5
        if (method_exists(get_parent_class(__CLASS__), 'assertIsString')) {
            return parent::assertIsString($actual, $message);
        }
        return self::assertInternalType('string', $actual, $message);
    }
}
