<?php

/**
 * This is an extension to {@link Zend_Crypt_Math} with added methods for
 * generating random float numbers between 0 and 1, and random strings.
 * These additions make it, in terms of feature parity, a backport of ZF2's
 * {@link https://docs.zendframework.com/zend-math/rand Zend\Math\Rand} class.
 *
 * Additionally, this class provides a set of characters lists for use with
 * {@link randString}.
 *
 * @category Zefram
 * @package  Zefram_Crypt
 * @author   xemlock
 * @uses     Zend_Crypt_Math
 */
class Zefram_Crypt_Math extends Zend_Crypt_Math
{
    const ALPHA_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const ALPHA_LOWER = 'abcdefghijklmnopqrstuvwxyz';
    const ALPHA       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const ALNUM       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const DIGITS      = '0123456789';
    const XDIGITS     = '0123456789ABCDEFabcdef';
    const BASE62      = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const BASE64      = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/';
    const BASE64URL   = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_';

    /**
     * {@inheritDoc}
     *
     * If $max is not provided, a result of {@link mt_getrandmax()} is used.
     *
     * @param int $min OPTIONAL, default 0
     * @param int $max OPTIONAL
     * @param bool $strong OPTIONAL
     */
    public static function randInteger($min = 0, $max = null, $strong = false)
    {
        if (null === $max) {
            $max = mt_getrandmax();
        }
        return parent::randInteger($min, $max, $strong);
    }

    /**
     * Generate a random float number between 0 and 1.
     *
     * @return float
     */
    public static function randFloat()
    {
        return parent::randInteger(0, mt_getrandmax()) / mt_getrandmax();
    }

    /**
     * Generate a random string of $length characters using the alphabet $charlist;
     * if not provided, the default alphabet is the Base64URL character set.
     *
     * @param  int $length
     * @param  string $charlist OPTIONAL    if character list is not expicitly
     *                                      given, use URL-safe Base64 alphabet
     * @return string
     */
    public static function randString($length, $charlist = self::BASE64URL)
    {
        $randmax = strlen($charlist) - 1;

        $length = max(0, $length);
        $output = '';

        while (strlen($output) < $length) {
            $output .= substr($charlist, self::randInteger(0, $randmax), 1);
        }

        return $output;
    }
}
