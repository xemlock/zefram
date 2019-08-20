<?php

/**
 * Wrapper around Zend_Json class for more flexible JSON encoding / decoding.
 *
 * @category Zefram
 * @package  Zefram_Json
 * @uses     Zend_Json
 */
abstract class Zefram_Json extends Zend_Json
{
    const CYCLE_CHECK       = 'cycleCheck';
    const PRETTY_PRINT      = 'prettyPrint';
    const UNESCAPED_SLASHES = 'unescapedSlashes';
    const UNESCAPED_UNICODE = 'unescapedUnicode';
    const HEX_TAG           = 'hexTag';
    const HEX_QUOT          = 'hexQuot';

    /**
     * @param mixed $value
     * @param bool|array $cycleCheck
     * @param array $options
     * @return string
     */
    public static function encode($value, $cycleCheck = false, $options = array())
    {
        $requirePhp53 = false;
        $requirePhp54 = false;

        if (is_array($cycleCheck)) {
            $options = $cycleCheck;
            $cycleCheck = false;
        }

        // cycle check applies only when encoding using Zend_Json_Encoder,
        // json_encode() has built-in recursion limit
        if (isset($options[self::CYCLE_CHECK])) {
            $cycleCheck = (bool) $options[self::CYCLE_CHECK];
            unset($options[self::CYCLE_CHECK]);
        }

        if (isset($options[self::HEX_TAG])) {
            $hexTag = (bool) $options[self::HEX_TAG];
            $requirePhp53 = true;
            unset($options[self::HEX_TAG]);
        } else {
            $hexTag = false;
        }

        if (isset($options[self::HEX_QUOT])) {
            $hexQuot = (bool) $options[self::HEX_QUOT];
            $requirePhp53 = true;
            unset($options[self::HEX_QUOT]);
        } else {
            $hexQuot = false;
        }

        if (isset($options[self::PRETTY_PRINT])) {
            $prettyPrint = (bool) $options[self::PRETTY_PRINT];
            $requirePhp54 = true;
            unset($options[self::PRETTY_PRINT]);
        } else {
            $prettyPrint = false;
        }

        if (isset($options[self::UNESCAPED_SLASHES])) {
            $unescapedSlashes = (bool) $options[self::UNESCAPED_SLASHES];
            $requirePhp54 = true;
            unset($options[self::UNESCAPED_SLASHES]);
        } else {
            $unescapedSlashes = false;
        }

        if (isset($options[self::UNESCAPED_UNICODE])) {
            $unescapedUnicode = (bool) $options[self::UNESCAPED_UNICODE];
            $requirePhp54 = true;
            unset($options[self::UNESCAPED_UNICODE]);
        } else {
            $unescapedUnicode = false;
        }

        $minVersion = $requirePhp54 ? '5.4.0' : ($requirePhp53 ? '5.3.0' : 0);
        $useNative = extension_loaded('json')
            && (!$minVersion || version_compare(PHP_VERSION, $minVersion, '>='));

        if ($useNative) {
            $flags = 0
                | ($hexTag ? JSON_HEX_TAG : 0)
                | ($hexQuot ? JSON_HEX_QUOT : 0)
                | ($unescapedSlashes ? JSON_UNESCAPED_SLASHES : 0)
                | ($unescapedUnicode ? JSON_UNESCAPED_UNICODE : 0)
                | ($prettyPrint ? JSON_PRETTY_PRINT : 0);

            $json = json_encode($value, $flags);

        } else {
            $json = parent::encode($value, $cycleCheck, $options);

            $search = array();
            $replace = array();

            if ($hexTag) {
                $search[]  = '<';
                $replace[] = '\u003C';

                $search[]  = '>';
                $replace[] = '\u003E';
            }

            if ($hexQuot) {
                $search[]  = '"';
                $replace[] = '\u0022';
            }

            if ($unescapedSlashes) {
                $search[]  = '\\/';
                $replace[] = '/';
            }

            if ($search) {
                $json = str_replace($search, $replace, $json);
            }

            if ($unescapedUnicode) {
                $json = Zend_Json_Decoder::decodeUnicodeString($json);
            }

            if ($prettyPrint) {
                $json = self::prettyPrint($json);
            }
        }

        // No string in JavaScript can contain a literal U+2028 or a U+2029
        // (line terminator and paragraph terminator respectively), so escape
        // them regardless of unescapedUnicode setting. The same behavior
        // was introduced in PHP 7.1 json_encode() function. More details:
        // - http://timelessrepo.com/json-isnt-a-javascript-subset
        // - http://php.net/manual/en/migration71.incompatible.php
        $json = strtr(
            $json,
            array(
                "\xE2\x80\xA8" => '\u2028',
                "\xE2\x80\xA9" => '\u2029',
            )
        );

        return $json;
    }

    /**
     * Pretty-print JSON string the same way as json_encode() function called
     * with JSON_PRETTY_PRINT flag would do.
     *
     * @param string $json
     * @param array $options
     * @return string
     */
    public static function prettyPrint($json, $options = array())
    {
        return preg_replace(
            '/(?<!\\\\)":(["\[\{]|\d|null|true|false)/i',
            '": \1',
            parent::prettyPrint($json, array_merge(array(
                'format' => 'txt',
                'indent' => '    ',
            ), $options))
        );
    }

    /**
     * @param string $filePath
     * @param int $objectDecodeType
     * @return mixed
     * @throws Zend_Json_Exception
     */
    public static function fromFile($filePath, $objectDecodeType = Zend_Json::TYPE_ARRAY)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Zend_Json_Exception("File does not exist or is not readable: $filePath");
        }
        if (($fileContents = @file_get_contents($filePath)) === false) {
            throw new Zend_Json_Exception("Cannot read file contents: $filePath");
        }
        return self::decode($fileContents, $objectDecodeType);
    }
}
