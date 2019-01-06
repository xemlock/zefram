<?php

/**
 * @category   Zefram
 * @package    Zefram_Filter
 * @subpackage Helper
 * @author     xemlock
 * @version    2014-07-05
 */
class Zefram_Filter_FileSize implements Zend_Filter_Interface
{
    const MODE_TRADITIONAL = 'traditional';     // 1 KB = 2^10 B
    const MODE_SI          = 'si';              // 1 KB = 10^3 B
    const MODE_IEC         = 'iec';             // 1 KiB = 2^10 B

    /**
     * Number of decimal digits to round to
     * @var int
     */
    protected $_precision;

    /**
     * One of MODE_ constants controlling radix and unit
     * @var string
     */
    protected $_mode;

    /**
     * Locale used for formatting the number
     * @var string
     */
    protected $_locale;

    /**
     * @param array|Zend_Config $options
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $method = 'set' . $key;
                if (method_exists($this, $method)) {
                    $this->${method}($value);
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getPrecision()
    {
        return $this->_precision;
    }

    /**
     * @param int $precision
     * @return Zefram_Filter_FileSize
     */
    public function setPrecision($precision)
    {
        $this->_precision = $precision;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * @param string $mode
     * @return Zefram_Filter_FileSize
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * @param string $locale
     * @return Zefram_Filter_FileSize
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
        return $this;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function filter($value)
    {
        return self::filterStatic($value, array(
            'precision' => $this->_precision,
            'mode'      => $this->_mode,
        ));
    }

    /**
     * @param  mixed $bytes     Number of bytes to format
     * @param  array $options   Array of options controlling filter behavior. Supported options:
     *                          <ul>
     *                              <li>precision - Number of decimal digits to round to,
     *                              <li>mode - One of MODE_ constants controlling radix and unit
     *                              <li>locale - Locale used for formatting the number
     *                          </ul>
     * @return string
     */
    public static function filterStatic($bytes, array $options = array())
    {
        $precision = isset($options['precision']) ? intval($options['precision']) : 0;
        $mode = isset($options['mode']) ? $options['mode'] : self::MODE_TRADITIONAL;
        $locale = isset($options['locale']) ? $options['locale'] : null;

        $bytes = floor($bytes);
        $radix = 1024;
        $iec = false;

        switch ($mode) {
            case self::MODE_TRADITIONAL:
                break;

            case self::MODE_IEC:
                $iec = true;
                break;

            case self::MODE_SI:
                $radix = 1000;
                break;

            default:
                throw new Zend_Filter_Exception("Invalid mode: '$mode'");
        }

        if ($iec) {
            $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        } else {
            $units = array('B', 'KB',  'MB',  'GB',  'TB',  'PB',  'EB',  'ZB',  'YB');
        }

        $idx = 0;
        $end = count($units) - 1;

        while ($bytes > $radix) {
            $bytes /= $radix;
            if ($idx == $end) {
                break;
            }
            ++$idx;
        }

        $fileSize = round($bytes, $precision);

        if ($locale) {
            $fileSize = Zend_Locale_Format::getFloat($fileSize, array('locale' => $locale));
        }

        return $fileSize . ' ' . $units[$idx];
    }
}
