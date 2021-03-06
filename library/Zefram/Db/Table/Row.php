<?php

/**
 * Features Zend_Db_Table_Row does not provide:
 * 1. Methods to determine the state of row: whether it is modified (isModified)
 *    or to get the list of modified columns (getModified) or if it stored in
 *    the database (isStored).
 * 2. Only assignments of different values are recognized of modifications, i.e.
 *    setting column value to an identical value does not count as modification
 * 3. Table factory depends on table instance row is attached to, no static
 *    method of Zend_Db_Table_Abstract is directly called.
 * 4. Ability to get or set referenced parent rows identified by a rule key.
 *    Such rows are available for future use.
 * 5. Columns can be automatically loaded on demand.
 *
 * 2014-10-23
 *          - reimplemented _refresh() so that it does not involve creation
 *            of a temporary row instance
 *          - added _postLoad()
 *          - added setModified() for better control of which columns should
 *            be persisted to database
 *
 * 2014-04-15
 *          - support for setting referenced rows by assignment to their
 *            corresponding ruleKeys
 *          - fixed fetching referenced rows when values of referencing column
 *            are changed
 */
class Zefram_Db_Table_Row extends Zend_Db_Table_Row
{
    /**
     * @var string
     */
    protected $_tableClass = 'Zefram_Db_Table';

    /**
     * Available row columns. For usability reasons column names are stored
     * as keys, array values are ignored.
     *
     * @var array
     */
    protected $_cols;

    /**
     * If true, this row is in the process of saving.
     *
     * @var bool
     */
    protected $_isSaving = false;

    /**
     * Referenced parent rows.
     *
     * @var array
     */
    protected $_parentRows = array();

    /**
     * @var array
     */
    protected $_parentRowsKeyCache;

    /**
     * @var
     */
    protected $_dependentRowsets;

    /**
     * Constructor. See {@see Zend_Db_Table_Row_Abstract::__construct()} for
     * more details.
     *
     * @param  array $config OPTIONAL
     * @return void
     * @throws Zend_Db_Table_Row_Exception
     */
    public function __construct(array $config = array())
    {
        if (!isset($config['table']) || !$config['table'] instanceof Zend_Db_Table_Abstract) {
            if ($this->_tableClass !== null) {
                $config['table'] = $this->_getTableFromString($this->_tableClass);
            } else {
                throw new Zend_Db_Table_Row_Exception('Table not provided');
            }
        }

        parent::__construct($config);

        $this->_setupCols();

        // if this row is marked as stored (i.e. exists in the database)
        // run post-load logic
        if (count($this->_cleanData)) {
            $this->_postLoad();
        }
    }

    public function setTable(Zend_Db_Table_Abstract $table = null)
    {
        $result = parent::setTable($table);
        $this->_setupCols();

        return $result;
    }

    protected function _setupCols()
    {
        $table = $this->_getTable();

        if (null === $table) {
            $this->_cols = null;
        } else {
            $this->_cols = array_flip($table->info(Zend_Db_Table_Abstract::COLS));
        }
    }

    /**
     * Allows post-load logic to be applied to row. Subclasses may override
     * this method.
     *
     * This method is called after a row is loaded from the database.
     *
     * @return void
     */
    protected function _postLoad()
    {}

    /**
     * @param  string $columnName
     * @return bool
     */
    public function hasColumn($columnName)
    {
        return $this->_hasColumn($this->_transformColumn($columnName));
    }

    /**
     * For internal use, contrary to {@see hasColumn()} it operates on an
     * already transformed column name.
     *
     * @param  string $transformedColumnName
     * @return bool
     */
    protected function _hasColumn($transformedColumnName)
    {
        return isset($this->_cols[$transformedColumnName]);
    }

    /**
     * Is this row stored in the database.
     *
     * @return bool
     */
    public function isStored()
    {
        return !empty($this->_cleanData);
    }

    /**
     * Does this row have modified fields, or has a specific field been
     * modified?
     *
     * @param  string $columnName OPTIONAL
     * @return bool
     */
    public function isModified($columnName = null)
    {
        if (null === $columnName) {
            return 0 < count($this->_modifiedFields);
        }

        $columnName = $this->_transformColumn($columnName);
        return isset($this->_modifiedFields[$columnName]);
    }

    /**
     * Retrieve an array of modified fields and associated values.
     *
     * @return array
     */
    public function getModified()
    {
        $modified = array();
        foreach ($this->_modifiedFields as $columnName => $value) {
            $modified[$columnName] = $this->_data[$columnName];
        }
        return $modified;
    }

    /**
     * This function is intended for internal use, you are free to use
     * it as long as you know what you're doing.
     *
     * Marking column as modified will essentially make it to update
     * value in the database.
     *
     * @param  string $columnName
     * @return Zefram_Db_Table_Row
     */
    public function setModified($columnName)
    {
        $columnName = $this->_transformColumn($columnName);

        if (!$this->_hasColumn($columnName)) {
            throw new Zend_Db_Table_Row_Exception(sprintf(
                'Specified column "%s" is not in the row', $columnName
            ));
        }

        $this->_modifiedFields[$columnName] = true;
        return $this;
    }

    /**
     * Gets the Zend_Db_Adapter_Abstract from the table this row is
     * connected to.
     *
     * @return Zend_Db_Adapter_Abstract
     * @throws Zend_Db_Table_Row_Exception
     */
    public function getAdapter()
    {
        return $this->_getTable()->getAdapter();
    }

    /**
     * Fetches value for given columns, which effectively re-initializes
     * values of this columns.
     *
     * @param  string|array $transformedColumnNames
     * @return mixed
     * @throws Zend_Db_Table_Row_Exception
     */
    protected function _fetchColumns($transformedColumnNames)
    {
        $table = $this->_getTable();
        $db = $table->getAdapter();

        $value = null;

        $select = $db->select();
        $select->from(
            $table->info(Zend_Db_Table_Abstract::NAME),
            (array) $transformedColumnNames
        );

        foreach ($this->_getWhereQuery(false) as $cond) {
            $select->where($cond);
        }

        foreach ($db->fetchRow($select) as $column => $value) {
            $this->_data[$column] = $value;
            $this->_cleanData[$column] = $value;
            unset($this->_modifiedFields[$column]);
        }

        // return the last fetched value
        return $value;
    }

    /**
     * Is value for given column present.
     *
     * @param  string $transformedColumnName
     * @return bool
     */
    protected function _isColumnLoaded($transformedColumnName)
    {
        return array_key_exists($transformedColumnName, $this->_data);
    }

    /**
     * Ensure all values of given columns are present.
     *
     * @param  string|array $transformedColumnNames
     * @return void
     */
    protected function _ensureLoaded($transformedColumnNames)
    {
        $missingCols = null; // lazy array initialization

        foreach ((array) $transformedColumnNames as $col) {
            // columns in the reference map are expected to be already
            // transformed
            if (!$this->_isColumnLoaded($col)) {
                $missingCols[] = $col;
            }
        }

        if ($missingCols) {
            $this->_fetchColumns($missingCols);
        }
    }

    /**
     * Is reference to parent row identified by rule name defined in the
     * parent table.
     *
     * @param  string $ruleKey
     * @return bool
     * @throws Exception
     */
    public function hasReference($ruleKey)
    {
        try {
            return (bool) $this->_getReference($ruleKey);
        } catch (Exception $e) {
        }
        return false;
    }

    /**
     * Get reference rule matching the given key.
     *
     * @param  string $ruleKey
     * @return array
     * @throws Zend_Db_Table_Row_Exception
     */
    protected function _getReference($ruleKey)
    {
        $ruleKey = (string) $ruleKey;
        $referenceMap = $this->_getTable()->info(Zend_Db_Table_Abstract::REFERENCE_MAP);

        if (isset($referenceMap[$ruleKey])) {
            return $referenceMap[$ruleKey];
        }

        throw new Zend_Db_Table_Row_Exception(sprintf(
            'No reference identified by rule "%s" defined in table %s',
            $ruleKey, get_class($this->_getTable())
        ));
    }

    /**
     * @param  string|array $rule
     * @return array
     * @throws Zefram_Db_Table_Row_InvalidArgumentException
     */
    protected function _getReferenceColumnMap($rule)
    {
        if (!is_array($rule)) {
            $rule = $this->_getReference($rule);
        }

        $columnMap = array_combine(
            (array) $rule[Zend_Db_Table_Abstract::COLUMNS],
            (array) $rule[Zend_Db_Table_Abstract::REF_COLUMNS]
        );

        if (false === $columnMap) {
            throw new Zefram_Db_Table_Row_InvalidArgumentException(sprintf(
                "Reference to table %s has invalid column cardinality",
                $rule[Zend_Db_Table_Abstract::REF_TABLE_CLASS]
            ));
        }

        return $columnMap;
    }

    /**
     * @param  string $ruleKey
     * @return string
     */
    protected function _getParentRowKey($ruleKey)
    {
        if (empty($this->_parentRowsKeyCache[$ruleKey])) {
            $rule = $this->_getReference($ruleKey);

            $cols = (array) $rule[Zend_Db_Table_Abstract::COLUMNS];
            $this->_ensureLoaded($cols);

            $temp = array();
            foreach ($cols as $column) {
                $temp[$column] = Zefram_Db_Traits::normalizeValue($this->{$column});
            }

            // lazy array initialization
            $this->_parentRowsKeyCache[$ruleKey] = $ruleKey . '@' . serialize($temp);
        }
        return $this->_parentRowsKeyCache[$ruleKey];
    }

    /**
     * @param  string $ruleKey
     * @param  Zend_Db_Table_Row_Abstract|null $row
     * @return Zefram_Db_Table_Row
     * @throws Zend_Db_Table_Row_Exception
     */
    public function setParentRow($ruleKey, Zend_Db_Table_Row_Abstract $row = null)
    {
        $rule = $this->_getReference($ruleKey);

        if (null === $row) {
            // nullify columns that are referencing previous parent object
            // and do not belong to primary key
            $primary = array_flip((array) $this->_primary);
            foreach ($this->_getReferenceColumnMap($rule) as $column => $refColumn) {
                if (!isset($primary[$column])) {
                    $this->{$column} = null;
                }
            }
            $this->_unsetParentRow($ruleKey);
            return $this;
        }

        $refTable = $this->_getTableFromString($rule[Zend_Db_Table_Abstract::REF_TABLE_CLASS]);
        $rowClass = $refTable->getRowClass();

        if (!$row instanceof $rowClass) {
            throw new Zend_Db_Table_Row_Exception(sprintf(
                "Row referenced by rule '%s' must be an instance of %s",
                $ruleKey,
                $refTable->getRowClass()
            ));
        }

        // update columns in the current row referencing the newly assigned one
        // retrieve referenced columns first, to avoid leaving this object in
        // an invalid state if an exception is thrown
        $cols = array();
        foreach ($this->_getReferenceColumnMap($rule) as $column => $refColumn) {
            $cols[$column] = $row->{$refColumn};
        }
        foreach ($cols as $key => $value) {
            $this->{$key} = $value;
        }

        // referencing columns may have been changed, compute new reference key
        $this->_setParentRow($this->_getParentRowKey($ruleKey), $row);

        return $this;
    }

    /**
     * Retrieves referenced parent row according to given rule.
     *
     * @param  string $ruleKey
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getParentRow($ruleKey)
    {
        $ruleKey = (string) $ruleKey;

        // we must store values of referencing columns and the result they
        // correspond to (a referenced row or null), hence the computed
        // reference key
        $refKey = $this->_getParentRowKey($ruleKey);

        // check if row referenced by given rule is already present in the
        // _referencedRows collection
        if (array_key_exists($refKey, $this->_parentRows)) {
            return $this->_parentRows[$refKey];
        }

        return $this->_setParentRow($refKey, $this->_fetchParentRow($ruleKey));
    }

    /**
     * Fetch parent row identified by a given rule name.
     *
     * @param  string $ruleKey
     * @return Zend_Db_Table_Row_Abstract|null
     */
    protected function _fetchParentRow($ruleKey)
    {
        $ruleKey = (string) $ruleKey;

        // fetch referenced parent row from the database
        $rule = $this->_getReference($ruleKey);
        $cols = (array) $rule[Zend_Db_Table_Abstract::COLUMNS];

        // if all values of the foreign key are NULL, assume that there
        // is no parent row
        $emptyForeignKey = true;

        foreach ($cols as $col) {
            if (isset($this->_data[$col])) {
                $emptyForeignKey = false;
                break;
            }
        }

        if ($emptyForeignKey) {
            $row = null;
        } else {
            $row = $this->findParentRow(
                $rule[Zend_Db_Table_Abstract::REF_TABLE_CLASS],
                $ruleKey
            );
        }

        // if no referenced row was fetched and there was any non-NULL
        // column involved, report a referential integrity violation
        if (empty($row) && !$emptyForeignKey) {
            throw new Zefram_Db_Table_Row_Exception_ReferentialIntegrityViolation(sprintf(
                'Row referenced by rule "%s" defined in Table "%s" not found',
                $ruleKey,
                get_class($this->_getTable())
            ));
        }

        if ($row instanceof Zend_Db_Table_Row_Abstract) {
            return $row;
        }
        return null;
    }

    /**
     * @param  string $ruleKey
     * @return void
     */
    protected function _unsetParentRow($ruleKey)
    {
        if (($pos = strpos($ruleKey, '@')) !== false) {
            $prefix = substr($ruleKey, 0, $pos + 1);
        } else {
            $prefix = $ruleKey . '@';
        }
        $len = strlen($prefix);
        foreach ($this->_parentRows as $key => $value) {
            if (!strncmp($key, $prefix, $len)) {
                unset($this->_parentRows[$key]);
            }
        }
    }

    /**
     * @param  string $parentKey
     * @param  Zend_Db_Table_Row_Abstract $parentKey
     * @return Zend_Db_Table_Row_Abstract|null
     */
    protected function _setParentRow($parentKey, Zend_Db_Table_Row_Abstract $row = null)
    {
        // remove previously fetched parent row(s) for this fule
        $this->_unsetParentRow($parentKey);

        $this->_parentRows[$parentKey] = $row;

        // echo json_encode(array_keys($this->_parentRows)), "\n";

        return $row;
    }

    /**
     * Save all modified or not stored referenced rows.
     *
     * This method is called by {@link save()} before saving current row.
     *
     * @return void
     */
    protected function _saveParentRows()
    {
        foreach ($this->_parentRows as $key => $row) {
            if (!$row instanceof Zend_Db_Table_Row_Abstract || $row === $this) {
                continue;
            }

            // update values of columns referencing parent row
            list($ruleKey, ) = explode('@', $key, 2);

            // check if parent row is still referenced by this row, if not
            // detach it
            if ($this->_getParentRowKey($ruleKey) !== $key) {
                $this->_setParentRow($ruleKey, null);
                continue;
            }

            // check if parent row is modified or not yet stored in the database
            $isStored = count($row->_cleanData);
            $isModified = count($row->_modifiedFields);

            // persist parent row if neccessary
            if ($isModified || !$isStored) {
                $row->save();
            }

            foreach ($this->_getReferenceColumnMap($ruleKey) as $column => $refColumn) {
                $this->{$column} = $row->{$refColumn};
            }

            // store parent row under an _updated key_
            // here foreach loop operates on a copy of array, any items
            // added or removed will not affect iteration
            $this->_setParentRow($this->_getParentRowKey($ruleKey), $row);
        }
    }

    /**
     * Retrieve row field value.
     *
     * If the field name starts with an uppercase and a reference rule with
     * the same name exists, the row referenced by this rule is fetched from
     * the database and stored for later use.
     *
     * @param string $key
     * @throws Zefram_Db_Table_Row_InvalidArgumentException
     *     Number of columns defined in reference rule does not match the
     *     number of columns in the primary key of the parent table.
     * @throws Zefram_Db_Table_Row_Exception_ReferentialIntegrityViolation
     *     No referenced row was found even though columns containing the
     *     primary key of row in the parent table are marked as NOT NULL.
     */
    public function __get($key)
    {
        $columnName = $this->_transformColumn($key);

        // column value already available, return it
        if ($this->_isColumnLoaded($columnName)) {
            return $this->_data[$columnName];
        }

        // lazy column loading
        if ($this->_hasColumn($columnName)) {
            return $this->_fetchColumns($columnName);
        }

        // reference loading
        if ($this->hasReference($key)) {
            return $this->getParentRow($key);
        }

        throw new Zend_Db_Table_Row_Exception(sprintf(
            'Specified column "%s" is not in the row', $columnName
        ));
    }

    /**
     * Does not mark unchanged values as modified. Allows to set values for
     * fields which was not yet fetched from the database.
     */
    public function __set($columnName, $value)
    {
        $columnName = $this->_transformColumn($columnName);

        if (!array_key_exists($columnName, $this->_data)) {
            if ($this->_hasColumn($columnName)) {
                $this->_data[$columnName] = $value;
                $this->_modifiedFields[$columnName] = true;

            } elseif ($this->hasReference($columnName)) {
                $this->setParentRow($columnName, $value);

            } else {
                throw new Zend_Db_Table_Row_Exception(sprintf(
                    'Specified column "%s" is not in the row', $columnName
                ));
            }

            return;
        }

        $origData = $this->_data[$columnName];

        // when comparing with previous value check if both types match
        // to avoid undesired behavior caused by type convergence, i.e.
        // NULL == 0, NULL == "", 0 == ""
        if ($origData !== $value) {
            $this->_data[$columnName] = $value;
            $this->_modifiedFields[$columnName] = true;
        }

        // force recalculation of referenced rows identifiers
        $this->_parentRowsKeyCache = null;
    }

    /**
     * Test existence of field or reference. For more specific test
     * use {@see hasColumn()} or {@see hasReference()} methods.
     *
     * @param  string $columnName
     * @return bool
     */
    public function __isset($columnName)
    {
        return $this->hasColumn($columnName) || $this->hasReference($columnName);
    }

    public function __unset($columnName)
    {
        if (!$this->hasColumn($columnName) && $this->hasReference($columnName)) {
            unset($this->_parentRows[$columnName]);
            return $this;
        }
        return parent::__unset($columnName);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return array_merge(parent::__sleep(), array('_cols'));
    }

    /**
     * {@inheritDoc}
     *
     * This is a replacement implementation which does not involve an
     * unnecessary creation of a temporary row instance. After the successful
     * retrieval of row's data, the post-load logic is executed.
     */
    protected function _refresh()
    {
        $select = $this->_getTable()->select();
        $select->limit(1);

        foreach ($this->_getWhereQuery() as $key => $value) {
            if (is_int($key)) {
                $select->where($value);
            } else {
                $select->where($key, $value);
            }
        }

        $data = $select->query(Zend_Db::FETCH_ASSOC)->fetch();

        if (empty($data)) {
            throw new Zend_Db_Table_Row_Exception('Cannot refresh row from the database');
        }

        $this->_data = $data;
        $this->_cleanData = $data;
        $this->_modifiedFields = array();
        $this->_parentRowsKeyCache = null;

        $this->_postLoad();
    }

    /**
     * Sets all data in the row from an array.
     *
     * @param  array $data
     * @return Zend_Db_Table_Row_Abstract
     */
    public function setFromArray(array $data)
    {
        foreach ($data as $columnName => $value) {
            if ($this->hasColumn($columnName) || $this->hasReference($columnName)) {
                $this->__set($columnName, $value);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function save()
    {
        if ($this->_isSaving) {
            return false;
        }

        // mark this row as being during save process to avoid infinite
        // recursion of save() calls if cycle references (referenced rows
        // referencing this row) are present
        $this->_isSaving = true;
        $this->_saveParentRows();

        /**
         * Run pre-SAVE logic
         */
        $result = parent::save();

        $this->_getTable()->addToIdentityMap($this);

        $this->_isSaving = false;

        /**
         * Run post-SAVE logic
         */
        $this->_postSave();

        return $result;
    }

    /**
     * @return int
     */
    public function delete()
    {
        // Prior to deletion, remember primary key values to be used when
        // removing this row from the identity map.
        $primaryKey = $this->_getPrimaryKey(false);
        $result = parent::delete();

        if ($result) {
            $this->_getTable()->removeFromIdentityMap($primaryKey);
        }

        return $result;
    }

    /**
     * @param  bool $includeReferencedRows deprecated
     * @return array
     */
    public function toArray($includeReferencedRows = false)
    {
        $array = parent::toArray();

        if ($includeReferencedRows) {
            foreach ($this->_parentRows as $key => $row) {
                if ($row instanceof Zend_Db_Table_Row_Abstract) {
                    $array[$key] = $row->toArray($includeReferencedRows);
                }
            }
        }

        return $array;
    }

    /**
     * Whenever possible this method fetches row using find() method
     * rather fetchRow(), so that identity map can be utilized (if exists).
     *
     * @param  string|Zend_Db_Table_Abstract $parentTable
     * @param  string $ruleKey OPTIONAL
     * @param  Zend_Db_Table_Select $select OPTIONAL
     * @return Zend_Db_Table_Row_Abstract
     */
    public function findParentRow($parentTable, $ruleKey = null, Zend_Db_Table_Select $select = null)
    {
        $db = $this->_getTable()->getAdapter();

        if (is_string($parentTable)) {
            $parentTable = $this->_getTableFromString($parentTable);
        }

        if (!$parentTable instanceof Zend_Db_Table_Abstract) {
            throw new Zend_Db_Table_Row_Exception(sprintf(
                'Parent table must be a Zend_Db_Table_Abstract, but it is %s',
                is_object($parentTable) ? get_class($parentTable) : gettype($parentTable)
            ));
        }

        // no select, try to fetch referenced row via find() called on the
        // parent table
        if (null === $select) {
            $rule = $this->_prepareReference($this->_getTable(), $parentTable, $ruleKey);

            // mapping between local columns and columns in referenced table
            $columnMap = $this->_getReferenceColumnMap($rule);

            // if local columns compose complete primary key in the parent
            // table (as should be the case in most situations) use find()
            // to retrieve the parent row so that an identity map (if exists)
            // may be utilized
            $parentPrimaryKey = array();

            foreach ($columnMap as $column => $refColumn) {
                // access column via __get rather than _data to utilize lazy
                // loading when neccessary
                $parentPrimaryKey[$refColumn] = $this->{$column};
            }

            if (count($parentTable->info(Zend_Db_Table_Abstract::PRIMARY)) === count($parentPrimaryKey)) {
                return $parentTable->find($parentPrimaryKey)->current();
            }
        }

        return parent::findParentRow($parentTable, $ruleKey, $select);
    }

    /**
     * Retrieve an instance of the table this row is connected to or, if table
     * name given, instantiate a table of a this class.
     *
     * @return Zend_Db_Table_Abstract
     * @throws Zend_Db_Table_Row_Exception
     */
    protected function _getTable($tableName = null)
    {
        if (!$this->_connected || !$this->_table) {
            throw new Zend_Db_Table_Row_Exception('Cannot retrieve Table instance from a disconnected Row');
        }
        if (null === $tableName) {
            return $this->_table;
        }

        try {
            throw new Exception;
        } catch (Exception $e) {
            $trace = $e->getTrace();
            $last = reset($trace);
            trigger_error(sprintf(
                'Calling %s() with table name parameter is deprecated. Called in %s on line %d',
                __METHOD__, $last['file'], $last['line']
            ), E_USER_NOTICE);
        }

        return $this->_table->_getTableFromString($tableName);
    }

    /**
     * Instantiate a table of a given class using connected table as
     * a factory.
     *
     * @param  string $tableName
     * @return Zend_Db_Table_Abstract
     */
    protected function _getTableFromString($tableName)
    {
        $table = $this->_getTable();

        if ($table instanceof Zefram_Db_Table) {
            return $table->_getTableFromString($tableName);
        }

        return parent::_getTableFromString($tableName);
    }

    /**
     * Allows pre-save (insert or update) logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _save()
    {}

    /**
     * Allows post-save (insert or update) logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _postSave()
    {}
}
