<?php

/**
 * Fix: loads helpers with Navigation_ prefix, otherwise they may conflict
 * with helpers of the same names but not in Navigation namespace.
 *
 * @property Zend_View_Abstract|Zefram_View_Abstract $view
 * @method Zefram_View_Helper_Navigation_Menu menu()
 */
class Zefram_View_Helper_Navigation extends Zend_View_Helper_Navigation
{
    const NS = 'Zefram_View_Helper_Navigation';

    public function findHelper($proxy, $strict = true)
    {
        if (!$this->view->getPluginLoader('helper')->getPaths(self::NS)) {
            $paths = $this->view->getHelperPaths();
            $this->view->setHelperPath(null);
            $this->view->addHelperPath(str_replace('_', '/', parent::NS), parent::NS);
            $this->view->addHelperPath(str_replace('_', '/', self::NS), self::NS);

            foreach ($paths as $ns => $path) {
                $this->view->addHelperPath($path, $ns);
            }
        }
        if (stripos($proxy, 'Navigation_') !== 0) {
            $proxy = 'Navigation_' . ucfirst($proxy);
        }
        return parent::findHelper($proxy, $strict);
    }
}
