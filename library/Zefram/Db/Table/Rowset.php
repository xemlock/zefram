<?php

/**
 * Features Zend_Db_Table_Rowset does not provide:
 * - ability to extract values of given column from all rows
 * - convert rowset to an array of rows, as {@link toArray()} converts rowset to array of arrays
 */
class Zefram_Db_Table_Rowset extends Zend_Db_Table_Rowset
{
    protected function _loadAndReturnRow($position)
    {
        $row = parent::_loadAndReturnRow($position);
        $table = $this->getTable();

        if ($row && ($table instanceof Zefram_Db_Table)) {
            $table->addToIdentityMap($row);
        }

        return $row;
    }

    /**
     * Collect values of given column from the rows in this rowset
     *
     * The behavior of this method is similar to {@link array_column()}.
     *
     * @param  string|null $columnName  If NULL the complete array of rows is returned
     * @param  string $indexBy          The column to use as the index/keys for the returned array
     * @return array
     */
    public function collectColumn($columnName, $indexBy = null)
    {
        $values = array();

        foreach ($this as $row) {
            $key = $indexBy === null ? count($values) : $row->{$indexBy};
            $values[$key] = $columnName === null ? $row : $row->{$columnName};
        }

        return $values;
    }
}
