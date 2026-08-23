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

    public static function assertStringContainsString($needle, $haystack, $message = '')
    {
        // Since PHPUnit 7.5
        if (method_exists(get_parent_class(__CLASS__), 'assertStringContainsString')) {
            return parent::assertStringContainsString($needle, $haystack, $message);
        }
        return self::assertContains($needle, $haystack, $message);
    }

    public static function assertStringNotContainsString($needle, $haystack, $message = '')
    {
        // Since PHPUnit 7.5
        if (method_exists(get_parent_class(__CLASS__), 'assertStringNotContainsString')) {
            return parent::assertStringNotContainsString($needle, $haystack, $message);
        }
        return self::assertNotContains($needle, $haystack, $message);
    }

    public function expectNotToPerformAssertions()
    {
        // Since PHPUnit 7.2
        if (method_exists(get_parent_class(__CLASS__), 'expectNotToPerformAssertions')) {
            return parent::expectNotToPerformAssertions();
        }
        // For prior PHPUnit versions essentially it's a no-op
    }
}
