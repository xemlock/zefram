<?php

/**
 * @category Zefram
 * @package  Zefram_Stdlib
 */
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
     * @param mixed $b
     * @param bool $preserveNumericKeys
     */
    public static function merge(array $a, $b, $preserveNumericKeys = false)
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

    /**
     * Reduce the array to a single value using a callback function applied
     * for each array value (from left to right).
     *
     * The built-in {@link array_reduce()} does not provide key to the
     * callback function, which limits its usage.
     *
     * The callback function is expected to have the following signature:
     *
     * <pre>
     *   mixed callback ( mixed $result , mixed $item [ , int|string $key ] )
     * </pre>
     *
     * @param array $array
     * @param callable $callback
     * @param mixed $initial OPTIONAL
     * @return mixed
     */
    public static function reduce(array $array, $callback, $initial = null)
    {
        if (!is_callable($callback)) {
            throw new Zend_Stdlib_Exception_InvalidCallbackException('Invalid callback provided');
        }
        foreach ($array as $key => $value) {
            $initial = call_user_func($callback, $initial, $value, $key);
        }
        return $initial;
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
