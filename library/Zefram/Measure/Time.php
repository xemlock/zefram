<?php

/**
 * Time measure with added support for time values in HH:MM[:SS] format.
 *
 * @category Zefram
 * @package  Zefram_Measure
 * @author   xemlock
 */
class Zefram_Measure_Time extends Zend_Measure_Time
{
    public function setValue($value, $type = null, $locale = null)
    {
        if (($type !== null) && Zend_Locale::isLocale($type, null, false)) {
            $locale = $type;
            $type = null;
        }

        if (is_string($value) && strpos($value, ':') !== false) {
            try {
                $time = Zend_Locale_Format::getTime($value, array('locale' => $locale));
                $time += array('second' => 0);

            } catch(Exception $e) {
                throw new Zend_Measure_Exception($e->getMessage(), $e->getCode(), $e);
            }

            $value = $time['hour'] * 3600 + $time['minute'] * 60 + $time['second'];
            $type  = ($type !== null) ? $type : $this->getType();

            parent::setValue($value, self::SECOND, $locale);

            if (($type !== null) && $type !== self::SECOND) {
                $this->setType($type);
            }

            return $this;
        }

        return parent::setValue($value, $type, $locale);
    }
}
