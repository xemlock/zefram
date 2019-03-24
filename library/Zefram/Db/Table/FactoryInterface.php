<?php

interface Zefram_Db_Table_FactoryInterface
{
    /**
     * @param  string $name
     * @return Zend_Db_Table_Abstract|Zefram_Db_Table
     */
    public function getTable($name);
}
