<?php

/**
 * @category Zefram
 * @package  Zefram_Db
 * @uses     Zend_Db
 */
class Zefram_Db implements Zefram_Db_TransactionManager
{
    /**
     * @var Zefram_Db[]
     */
    protected static $_registry = array();

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_adapter;

    /**
     * @var Zefram_Db_Table_Factory
     */
    protected $_tableFactory;

    /**
     * @var int
     */
    protected $_transactionLevel = 0;

    /**
     * Factory for Db objects.
     *
     * @param  string|Zend_Config $adapter
     * @param  array|Zend_Config $config OPTIONAL
     * @return Zefram_Db
     */
    public static function factory($adapter, $config = array())
    {
        $adapter = Zend_Db::factory($adapter, $config);
        return new self($adapter);
    }

    /**
     * Utility method for retrieving Zefram_Db instance for given adapter.
     *
     * @param Zend_Db_Adapter_Abstract $adapter
     * @return Zefram_Db
     */
    public static function getInstance(Zend_Db_Adapter_Abstract $adapter)
    {
        $hash = spl_object_hash($adapter);
        if (isset(self::$_registry[$hash])) {
            return self::$_registry[$hash];
        }
        return new self($adapter);
    }

    /**
     * Constructor.
     *
     * @param Zend_Db_Adapter_Abstract|Zefram_Db_Table_FactoryInterface $adapter
     * @return void
     * @throws InvalidArgumentException
     */
    public function __construct($adapter)
    {
        if ($adapter instanceof Zefram_Db_Table_FactoryInterface) {
            $this->_adapter = $adapter->getAdapter();
            $this->_tableFactory = $adapter;
        } elseif ($adapter instanceof Zend_Db_Adapter_Abstract) {
            $this->_adapter = $adapter;
        } else {
            throw new InvalidArgumentException('Adapter must be either an instance of Zend_Db_Adapter_Abstract or Zefram_Db_Table_FactoryInterface');
        }

        self::$_registry[spl_object_hash($this->_adapter)] = $this;
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter() // {{{
    {
        return $this->_adapter;
    } // }}}

    /**
     * @return Zefram_Db_Table_FactoryInterface
     */
    public function getTableFactory() // {{{
    {
        if ($this->_tableFactory === null) {
            $this->_tableFactory = new Zefram_Db_Table_Factory($this->_adapter);
        }
        return $this->_tableFactory;
    } // }}}

    /**
     * Creates a Zefram_Db_Select instance using the current adapter.
     *
     * @return Zefram_Db_Select
     */
    public function select()
    {
        return new Zefram_Db_Select($this->_adapter);
    }

    /**
     * Creates a {@link Zend_Db_Expr} instance with params quoted into the expression.
     *
     * @param string $expr
     * @param array $params OPTIONAL
     * @return Zend_Db_Expr
     */
    public function expr($expr, $params = null)
    {
        if (null !== $bind) {
            $expr = Zefram_Db_Traits::bindParams($this, $expr, $params);
        }

        return new Zend_Db_Expr($expr);
    }

    /**
     * Proxy to adapter's quote() method.
     * See {@link Zend_Db_Adapter_Abstract::quote()} for details.
     */
    public function quote($value, $type = null)
    {
        return $this->_adapter->quote($value, $type);
    }

    /**
     * Proxy to adapter's quote() method.
     * See {@link Zend_Db_Adapter_Abstract::quoteIdentifier()} for details.
     */
    public function quoteIdentifier($ident, $auto = false)
    {
        return $this->_adapter->quoteIdentifier($ident, $auto);
    }

    /**
     * Proxy to adapter's quoteInto() method.
     * See {@link Zend_Db_Adapter_Abstract::quoteInto()} for details.
     */
    public function quoteInto($text, $value, $type = null, $count = null)
    {
        return $this->_adapter->quoteInto($text, $value, $type, $count);
    }

    /**
     * @return Zefram_Db
     */
    public function beginTransaction() // {{{
    {
        if ($this->_transactionLevel === 0) {
            // increase level counter _after_ beginning transaction,
            // in case an exception is thrown
            $this->_adapter->beginTransaction();
            ++$this->_transactionLevel;
        }
        return $this;
    } // }}}

    /**
     * @return Zefram_Db
     */
    public function rollBack() // {{{
    {
        if ($this->_transactionLevel === 1) {
            $this->_adapter->rollBack();
        }
        --$this->_transactionLevel;
        return $this;
    } // }}}

    /**
     * @return Zefram_Db
     */
    public function commit() // {{{
    {
        if ($this->_transactionLevel === 1) {
            $this->_adapter->commit();
        }
        --$this->_transactionLevel;
        return $this;
    } // }}}

    /**
     * @return bool
     */
    public function inTransaction() // {{{
    {
        return ($this->_transactionLevel > 0);
    } // }}}

    /**
     * @return Zend_Db_Table_Abstract|Zefram_Db_Table
     */
    public function getTable($name) // {{{
    {
        return $this->getTableFactory()->getTable($name);
    } // }}}

    /**
     * @param  string $prefix
     * @return Zefram_Db
     */
    public function setTablePrefix($prefix) // {{{
    {
        $this->getTableFactory()->setTablePrefix($prefix);
        return $this;
    } // }}}

    /**
     * @return string
     */
    public function getTablePrefix() // {{{
    {
        return $this->getTableFactory()->getTablePrefix();
    } // }}}

    /**
     * Proxy to adapter's fetchAll() method.
     * See {@link Zend_Db_Adapter_Abstract::fetchAll()} for details.
     *
     * @param string|Zend_Db_Select $sql  An SQL SELECT statement.
     * @param mixed                 $bind Data to bind into SELECT placeholders.
     * @param mixed                 $fetchMode Override current fetch mode.
     * @return array
     */
    public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
        return $this->getAdapter()->fetchAll($sql, $bind, $fetchMode);
    }

    /**
     * Proxy to adapter's fetchRow() method.
     * See {@link Zend_Db_Adapter_Abstract::fetchRow()} for details.
     *
     * @param string|Zend_Db_Select $sql An SQL SELECT statement.
     * @param mixed                 $bind Data to bind into SELECT placeholders.
     * @param mixed                 $fetchMode Override current fetch mode.
     * @return mixed Array, object, or scalar depending on fetch mode.
     */
    public function fetchRow($sql, $bind = array(), $fetchMode = null)
    {
        return $this->getAdapter()->fetchRow($sql, $bind, $fetchMode);
    }

    /**
     * Proxy to adapter's fetchAssoc() method.
     * See {@link Zend_Db_Adapter_Abstract::fetchAssoc()} for details.
     *
     * @param string|Zend_Db_Select $sql An SQL SELECT statement.
     * @param mixed                 $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchAssoc($sql, $bind = array())
    {
        return $this->getAdapter()->fetchAssoc($sql, $bind);
    }

    /**
     * Proxy to adapter's fetchCol() method.
     * See {@link Zend_Db_Adapter_Abstract::fetchCol()} for details.
     *
     * @param string|Zend_Db_Select $sql An SQL SELECT statement.
     * @param mixed                 $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchCol($sql, $bind = array())
    {
        return $this->getAdapter()->fetchCol($sql, $bind);
    }

    /**
     * Proxy to adapter's fetchPairs() method.
     * See {@link Zend_Db_Adapter_Abstract::fetchPairs()} for details.
     *
     * @param string|Zend_Db_Select $sql An SQL SELECT statement.
     * @param mixed                 $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchPairs($sql, $bind = array())
    {
        return $this->getAdapter()->fetchPairs($sql, $bind);
    }

    /**
     * Proxy to adapter's fetchOne() method.
     * See {@link Zend_Db_Adapter_Abstract::fetchOne()} for details.
     *
     * @param string|Zend_Db_Select $sql An SQL SELECT statement.
     * @param mixed                 $bind Data to bind into SELECT placeholders.
     * @return mixed
     */
    public function fetchOne($sql, $bind = array())
    {
        return $this->getAdapter()->fetchOne($sql, $bind);
    }

    protected static $_tableRegistry;

    /**
     * Quote parameters into given string using named parameters notation,
     * regardless of whether database adapter supports named parameters or not.
     *
     * @param  Zend_Db_Adapter_Abstract $db
     * @param  string $string
     * @param  array $params
     * @return string
     * @throws InvalidArgumentException
     */
    public static function quoteParamsInto(Zend_Db_Adapter_Abstract $db, $string, array $params) // {{{
    {
        $replace = array();
        $position = 0;

        // build replacement pairs for positional and named parameters
        foreach ($params as $name => $value) {
            $quoted = $db->quote($value);

            if ($name === $position) {
                $replace['?' . ($position + 1)] = $quoted;

            } elseif (preg_match('/^[_A-Z][_0-9A-Z]*$/i', $name)) {
                $replace[':' . $name] = $quoted;
                $replace['?' . ($position + 1)] = $quoted;

            } else {
                throw new InvalidArgumentException(sprintf(
                    'Invalid parameter name: %s', $name
                ));
            }

            ++$position;
        }

        // use strtr() and not str_replace() to avoid recursive replacements
        return strtr($string, $replace);
    } // }}}

    /**
     * @param  Zend_Db_Adapter_Abstract $db
     * @param  string $string
     * @return string
     */
    public static function quoteEmbeddedIdentifiers(Zend_Db_Adapter_Abstract $db, $string) // {{{
    {
        if ($string instanceof Zend_Db_Expr) {
            return $string;
        }
        return preg_replace_callback(
            '/(?P<table>[_a-z][_a-z0-9]*)\.(?P<column>[_a-z][_a-z0-9]*)/i',
            self::_quoteEmbeddedIdentifiersCallback($db), $string
        );
    } // }}}

    /**
     * @param Zend_Db_Adapter_Abstract|array $dbOrMatch
     * @return string|callback
     * @internal
     */
    protected static function _quoteEmbeddedIdentifiersCallback($dbOrMatch) // {{{
    {
        static $db;

        if ($dbOrMatch instanceof Zend_Db_Adapter_Abstract) {
            $db = $dbOrMatch;
            return array(__CLASS__, __FUNCTION__);
        }

        return $db->quoteIdentifier($dbOrMatch['table']) . '.' . $db->quoteIdentifier($dbOrMatch['column']);
    } // }}}

    public static function getTable2($className, Zend_Db_Adapter_Abstract $db = null, $addPrefix = true)
    {
        if (null === $db) {
            $db = Zefram_Db_Table::getDefaultAdapter();
        }
        if (null === $db) {
            throw new Exception('No default database adapter found');
        }

        $fullClassName = $className;

        $adapterId = spl_object_hash($db);

        if (!isset(self::$_tableRegistry[$adapterId][$fullClassName])) {
            if (class_exists($fullClassName, true)) {
                // ok, class found
                $dbTable = new $fullClassName(array(
                    'db' => $db,
                ));
            } else {
                // no class found, simulate it with basic Db_Table with only
                // table name set
                $tableName = self::classToTable($className);
                $dbTable = new Zefram_Db_Table(array(
                    'db' => $db,
                    'name' => $tableName,
                ));
            }
            self::$_tableRegistry[$adapterId][$fullClassName] = $dbTable;
        }
        return self::$_tableRegistry[$adapterId][$fullClassName];
    }

    // convert camel-case to underscore separated
    public static function classToTable($className)
    {
        return strtolower(
            substr($className, 0, 1) .
            preg_replace('/([A-Z])/', '_$1', substr($className, 1))
        );
    }
}
