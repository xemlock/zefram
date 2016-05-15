<?php

class Zefram_Validate_Db_NoRecordExists extends Zend_Validate_Db_NoRecordExists
{
    /**
     * @param string|Zend_Db_Table_Abstract $table
     * @return Zend_Validate_Db_Abstract
     */
    public function setTable($table)
    {
        if ($table instanceof Zend_Db_Table_Abstract) {
            $this->setAdapter($table->getAdapter());
            $this->setSchema($table->info(Zend_Db_Table_Abstract::SCHEMA));
            $table = $table->info(Zend_Db_Table_Abstract::NAME);
        }
        return parent::setTable($table);
    }

    /**
     * @param  string|Zend_Db_Expr $field
     * @return Zefram_Validate_Db_RecordExists
     */
    public function setField($field)
    {
        if (!$field instanceof Zend_Db_Expr) {
            $field = (string) $field;
        }
        $this->_field = $field;
        return $this;
    }
}
