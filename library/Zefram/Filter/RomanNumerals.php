<?php

/**
 * Converts provided value to roman numerals.
 *
 * @category Zefram
 * @package  Zefram_Filter
 * @author   xemlock
 */
class Zefram_Filter_RomanNumerals implements Zend_Filter_Interface
{
    protected static $_numerals = array(
        1000 => 'M',
        900  => 'CM',
        500  => 'D',
        400  => 'CD',
        100  => 'C',
        90   => 'XC',
        50   => 'L',
        40   => 'XL',
        10   => 'X',
        9    => 'IX',
        5    => 'V',
        4    => 'IV',
        1    => 'I',
    );

    public function filter($value)
    {
        return self::filterStatic($value);
    }

    /**
     * @param int $value
     * @return string
     */
    public static function filterStatic($value)
    {
        $value = (int) $value;

        if ($value > 0) {
            $roman = '';
            foreach (self::$_numerals as $numeral => $symbol) {
                while ($value >= $numeral) {
                    $roman .= $symbol;
                    $value -= $numeral;
                }
            }
        } else {
            $roman = 'N';
        }

        return $roman;
    }
}
