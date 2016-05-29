<?php

/**
 * Filter for removing invalid UTF-8 sequences in input string.
 * Based on: http://stackoverflow.com/questions/8215050/#13695364
 *
 * @version 2014-11-07
 * @author  xemlock
 */
class Zefram_Filter_Utf8 implements Zend_Filter_Interface
{
    // REPLACEMENT CHARACTER is used to replace an unknown or unrepresentable character
    const REPLACEMENT_CHAR      = "\xEF\xBF\xBD";
    const REPLACEMENT_CODEPOINT = 0xFFFD;

    /**
     * @var string
     */
    protected $_substChar;

    /**
     * Set substitution character(s).
     *
     * @param  string|null $substChar
     * @return Zefram_Filter_Utf8
     */
    public function setSubstChar($substChar = null)
    {
        if ($substChar !== null) {
            $substChar = (string) $substChar;
        }
        $this->_substChar = $substChar;
        return $this;
    }

    /**
     * Get substitution character(s).
     *
     * @return string|null
     */
    public function getSubstChar()
    {
        return $this->_substChar;
    }

    /**
     * Remove invalid UTF-8 sequence from input string using an
     * instance based filter.
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        return self::filterStatic($value, $this->getSubstChar());
    }

    /**
     * Remove invalid UTF-8 sequence from input string.
     *
     * @param  string $value
     * @param  string $substChar OPTIONAL
     * @return string
     */
    public static function filterStatic($value, $substChar = null)
    {
        if (extension_loaded('mbstring')) {
            $filtered = self::_mbstringFilter($value);
        } else {
            $filtered = self::_regexFilter($value);
        }

        if (null !== $substChar) {
            $filtered = str_replace(self::REPLACEMENT_CHAR, $substChar, $filtered);
        }

        return self::_normalize($filtered);
    }

    /**
     * Filter out invalid UTF-8 characters using mbstring library.
     *
     * Do not call this method directly, as it is public for testing purposes
     * only.
     *
     * @param string $value
     * @return mixed|string
     */
    public static function _mbstringFilter($value)
    {
        $prevSubstChar = mb_substitute_character();
        mb_substitute_character(self::REPLACEMENT_CODEPOINT);

        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        mb_substitute_character($prevSubstChar);

        return $value;
    }

    /**
     * Filter out invalid UTF-8 characters using regular expression.
     *
     * Do not call this method directly, as it is public for testing purposes
     * only.
     *
     * @param string $value
     * @return mixed
     */
    public static function _regexFilter($value)
    {
        // Implementation taken from http://stackoverflow.com/a/13695364
        $regex = '/
          ([\x00-\x7F]                       #   U+0000 -   U+007F
          |[\xC2-\xDF][\x80-\xBF]            #   U+0080 -   U+07FF
          | \xE0[\xA0-\xBF][\x80-\xBF]       #   U+0800 -   U+0FFF
          |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} #   U+1000 -   U+CFFF
          | \xED[\x80-\x9F][\x80-\xBF]       #   U+D000 -   U+D7FF
          | \xF0[\x90-\xBF][\x80-\xBF]{2}    #  U+10000 -  U+3FFFF
          |[\xF1-\xF3][\x80-\xBF]{3}         #  U+40000 -  U+FFFFF
          | \xF4[\x80-\x8F][\x80-\xBF]{2})   # U+100000 - U+10FFFF
          |(\xE0[\xA0-\xBF]                  #   U+0800 -   U+0FFF (invalid)
          |[\xE1-\xEC\xEE\xEF][\x80-\xBF]    #   U+1000 -   U+CFFF (invalid)
          | \xED[\x80-\x9F]                  #   U+D000 -   U+D7FF (invalid)
          | \xF0[\x90-\xBF][\x80-\xBF]?      #  U+10000 -  U+3FFFF (invalid)
          |[\xF1-\xF3][\x80-\xBF]{1,2}       #  U+40000 -  U+FFFFF (invalid)
          | \xF4[\x80-\x8F][\x80-\xBF]?)     # U+100000 - U+10FFFF (invalid)
          |(.)                               # invalid 1-byte
        /xs';

        // $matches[1]: valid character
        // $matches[2]: invalid 3-byte or 4-byte character
        // $matches[3]: invalid 1-byte
        return preg_replace_callback($regex, array(__CLASS__, '_regexFilterCallback'), $value);
    }

    /**
     * {@link _regexFilter()} helper function.
     *
     * @param array $matches
     * @return string
     */
    protected static function _regexFilterCallback(array $matches)
    {
        if (isset($matches[2]) || isset($matches[3])) {
            return self::REPLACEMENT_CHAR;
        }
        return $matches[1];
    }

    /**
     * Perform some UTF-8 normalizations
     *
     * @param string $string
     * @return string
     */
    public static function _normalize($string)
    {
        // use canonical forms of compound glyphs
        $string = strtr($string, array(
            '́e' => 'é',
        ));
        // remove control characters other than \t, \n and \r
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '$', $string);
        return $string;
    }
}
