<?php

/**
 * Resource for initializing database adapter and Zefram_Db wrapper.
 *
 * It supports all the options as {@link Zend_Application_Resource_Db}, and
 * the following additional ones:
 *
 *   resources.db.tablePrefix = <VALUE>
 *
 * As an initialization side effect it registers a Zefram_Db instance in
 * the bootstrap container and in the Zend_Registry under 'Zefram_Db' key.
 *
 * @category   Zefram
 * @package    Zefram_Application
 * @subpackage Resource
 * @author     xemlock
 */
class Zefram_Application_Resource_Db extends Zend_Application_Resource_Db
{
    /**
     * @var Zefram_Db
     */
    protected $_zeframDb;

    /**
     * @var string
     */
    protected $_tablePrefix;

    /**
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->_tablePrefix;
    }

    /**
     * @param string $tablePrefix
     * @return $this
     */
    public function setTablePrefix($tablePrefix)
    {
        $this->_tablePrefix = $tablePrefix;
        return $this;
    }

    /**
     * @return Zend_Db_Adapter_Abstract|null
     */
    public function getDbAdapter()
    {
        if (null !== ($zeframDb = $this->getZeframDb())) {
            $this->_db = $zeframDb->getAdapter();
        }
        return $this->_db;
    }

    /**
     * @return Zefram_Db|null
     */
    public function getZeframDb()
    {
        if (null === $this->_zeframDb &&
            null !== ($adapter = $this->getAdapter())
        ) {
            $zeframDb = Zefram_Db::factory($adapter, $this->getParams());
            $zeframDb->setTablePrefix($this->_tablePrefix);

            if ($this->isDefaultTableAdapter()) {
                Zend_Db_Table::setDefaultAdapter($zeframDb->getAdapter());
            }

            $bootstrap = $this->getBootstrap();
            if ($bootstrap instanceof Zend_Application_Bootstrap_BootstrapAbstract) {
                $bootstrap->getContainer()->Zefram_Db = $zeframDb;
            }

            Zend_Registry::set('Zefram_Db', $zeframDb);

            return $this->_zeframDb = $zeframDb;
        }
        return null;
    }

    /**
     * @return Zend_Db_Adapter_Abstract|null
     */
    public function init()
    {
        return $this->getDbAdapter();
    }
}
