<?php

/**
 * @deprecated Use {@link Zefram_Crypt_Math} instead.
 */
abstract class Zefram_Random
{
    const ALPHA_UPPER = Zefram_Crypt_Math::ALPHA_UPPER;
    const ALPHA_LOWER = Zefram_Crypt_Math::ALPHA_LOWER;
    const ALPHA       = Zefram_Crypt_Math::ALPHA;
    const ALNUM       = Zefram_Crypt_Math::ALNUM;
    const DIGITS      = Zefram_Crypt_Math::DIGITS;
    const XDIGITS     = Zefram_Crypt_Math::XDIGITS;
    const BASE64      = Zefram_Crypt_Math::BASE64;
    const BASE64URL   = Zefram_Crypt_Math::BASE64URL;

    /**
     * @deprecated Use {@link Zefram_Crypt_Math::randInteger()} instead
     */
    public static function getInteger($min, $max = null)
    {
        return Zefram_Crypt_Math::randInteger($min, $max);
    }

    /**
     * @deprecated Use {@link Zefram_Crypt_Math::randFloat()} instead
     */
    public static function getFloat()
    {
        return Zefram_Crypt_Math::randFloat();
    }

    /**
     * @deprecated Use {@link Zefram_Crypt_Math::randString()} instead
     */
    public static function getString($length, $chars = self::BASE64URL)
    {
        return Zefram_Crypt_Math::randString($length, $chars);
    }
}
