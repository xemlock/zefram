<?php

/**
 * Enhancements from Zend_Validate_Db_Abstract:
 * - field can be given as a Zend_Db_Expr instance
 * - table can be given as a Zend_Db_Table_Abstract instance, schema and adapter are taken from it
 * - exclude can be more general when given as array
 * - messages can be configured in constructor
 */
abstract class Zefram_Validate_Db_Abstract extends Zend_Validate_Db_Abstract
{
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        parent::__construct($options);

        if (!is_array($options)) {
            return;
        }

        if (array_key_exists('messages', $options)) {
            $this->setMessages((array) $options['messages']);
        }
    }

    /**
     * @param  string|Zend_Db_Table_Abstract $table
     * @return Zefram_Validate_Db_Abstract
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
     * @return Zefram_Validate_Db_Abstract
     */
    public function setField($field)
    {
        if (!$field instanceof Zend_Db_Expr) {
            $field = (string) $field;
        }
        $this->_field = $field;
        return $this;
    }

    /**
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
        if (null === $this->_select) {
            if (is_array($this->_exclude)) {
                if (!isset($this->_exclude['field']) || !isset($this->_exclude['value'])) {
                    $exclude = $this->_exclude;
                    $this->_exclude = null;
                }
            }

            $select = parent::getSelect();

            if (isset($exclude)) {
                foreach ($exclude as $key => $value) {
                    if (is_int($key)) {
                        $select->where($value);
                    } else {
                        $select->where($key, $value);
                    }
                }
                $this->_exclude = $exclude;
            }

            $this->_select = $select;
        }
        return $this->_select;
    }
}
