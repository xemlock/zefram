<?php

/**
 * Confirms a record exists in a table.
 *
 * @category   Zefram
 * @package    Zefram_Validate
 * @uses       Zefram_Validate_Db_Abstract
 */
class Zefram_Validate_Db_RecordExists extends Zefram_Validate_Db_Abstract
{
    public function isValid($value)
    {
        $valid = true;
        $this->_setValue($value);

        $result = $this->_query($value);
        if (!$result) {
            $valid = false;
            $this->_error(self::ERROR_NO_RECORD_FOUND);
        }

        return $valid;
    }
}
