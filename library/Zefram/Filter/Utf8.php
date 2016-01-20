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
     * Remove invalid UTF-8 sequence from input string.
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (extension_loaded('mbstring')) {
            return $this->_mbstringFilter($value);
        }
        return $this->_regexFilter($value);
    }

    /**
     * Filter out invalid UTF-8 characters using mbstring library.
     *
     * Do not call this method directly, call {@link filter()} instead, as
     * it is public for testing purposes only.
     *
     * @param $value
     * @return mixed|string
     */
    public function _mbstringFilter($value)
    {
        $prevSubstChar = mb_substitute_character();
        mb_substitute_character(self::REPLACEMENT_CODEPOINT);

        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        mb_substitute_character($prevSubstChar);

        if (null !== $this->_substChar) {
            $value = str_replace(self::REPLACEMENT_CHAR, $this->_substChar, $value);
        }

        return $value;
    }

    /**
     * Filter out invalid UTF-8 characters using regular expression.
     *
     * Do not call this method directly, call {@link filter()} instead, as
     * it is public for testing purposes only.
     *
     * @param $value
     * @return mixed
     */
    public function _regexFilter($value)
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
        return preg_replace_callback($regex, array($this, '_regexFilterCallback'), $value);
    }

    /**
     * @internal
     */
    protected function _regexFilterCallback(array $matches)
    {
        if (isset($matches[2]) || isset($matches[3])) {
            if (null !== $this->_substChar) {
                return $this->_substChar;
            }
            return self::REPLACEMENT_CHAR;
        }

        return $matches[1];
    }

    /**
     * Call this filter in a static way.
     *
     * @param  string $value
     * @param  string $substChar OPTIONAL
     * @return string
     * @deprecated Use instance based filtering
     */
    public static function filterStatic($value, $substChar = null)
    {
        $filter = new self();
        $filter->setSubstChar($substChar);
        return $filter->filter($value);
    }
}
