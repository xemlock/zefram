<?php

/**
 * resources.tableManager.tablePrefix =
 * resources.tableManager.tableClass = Zefram_Db_Table
 * resources.tableManager.adapter = ... ; string only if MultiDb is present
 * TODO ability to define db adapter here
 */
class Zefram_Application_Resource_Tablemanager
    extends Zend_Application_Resource_ResourceAbstract
{
    protected $_manager;

    public function init()
    {
        return $this->getTableManager();
    }

    public function getTableManager()
    {
        if ($this->_manager === null) {
            $bootstrap = $this->getBootstrap();
            if (($bootstrap instanceof Zend_Application_Bootstrap_ResourceBootstrapper) &&
                (method_exists($bootstrap, '_initDb') || $bootstrap->hasPluginResource('Db'))
            ) {
                $bootstrap->bootstrap('Db');
            } else {
                // TODO try to detect default table adapter if multiDb resource
                // is available
                throw new Exception('Db resource is not avaliable');
            }

            $manager = new Zefram_Db_TableProvider($bootstrap->getResource('Db'));
            foreach ($this->getOptions() as $key => $value) {
                $method = 'set' . $key;
                if (method_exists($manager, $method)) {
                    $manager->{$method}($value);
                }
            }

            $this->_manager = $manager;
        }
        return $this->_manager;
    }
}
