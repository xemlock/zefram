<?php

abstract class Zefram_Stdlib_ArrayUtils
{
    /**
     * Merge arrays recursively.
     *
     * The built-in {@link array_merge_recursive()} is somewhat inconvenient,
     * because it merges values with the same string keys into a single array
     * instead of overwriting them with the values from the seconda array.
     *
     * @param array $a
     * @param array $b
     * @param bool $preserveNumericKeys
     */
    public static function merge(array $a, array $b, $preserveNumericKeys = false)
    {
        if (is_array($b)) {
            foreach ($b as $key => $value) {
                if (is_int($key) && !$preserveNumericKeys) {
                    $a[] = $value;
                } elseif (is_array($b[$key])) {
                    $a[$key] = isset($a[$key]) && is_array($a[$key])
                        ? self::merge($a[$key], $b[$key])
                        : $b[$key];
                } else {
                    $a[$key] = $value;
                }
            }
        }
        return $a;
    }

    public static function first(array $a)
    {
        return reset($a);
    }

    public static function last(array $a)
    {
        return end($a);
    }

    const CASE_LOWER      = 0;
    const CASE_UPPER      = 1;
    const CASE_CAMEL      = 2;
    const CASE_UNDERSCORE = 4;

    /**
     * Changes the case of all keys in an array.
     *
     * @param  array $array
     * @param  int $case
     * @return array|false
     */
    public static function changeKeyCase(array $array, $case = self::CASE_LOWER)
    {
        $case = (int) $case;

        if ($case === self::CASE_LOWER || $case === self::CASE_UPPER) {
            return array_change_key_case($array, $case);
        }

        // underscore to camelcase
        if ($case & self::CASE_CAMEL) {
            $result = array();
            foreach ($array as $key => $value) {
                $key = strtolower($key);
                $key = str_replace('_', ' ', $key);
                $key = ucwords($key);
                $key = str_replace(' ', '', $key);
                $result[$key] = $value;
            }
            return $result;
        }

        // camelcase to underscore
        if ($case & self::CASE_UNDERSCORE) {
            $toupper = $case & self::CASE_UPPER;
            $result = array();
            foreach ($array as $key => $value) {
                $key = preg_replace('/([0-9a-z])([A-Z])/', '$1_$2', $key);
                $key = $toupper ? strtoupper($key) : strtolower($key);
                $result[$key] = $value;
            }
            return $result;
        }

        return false;
    }


}
