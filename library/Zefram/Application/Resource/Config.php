<?php

/**
 * Config options:
 *
 *      resources.config = path
 *      resources.config[] = path
 *
 * This class can be used to set up an auxiliary configuration beside the main
 * configuration file (application.ini). This can be particularily useful when
 * you want to separate application wiring (managed by the programmer) and site
 * configuration (managed by administrator).
 *
 * @uses Zefram_Config
 */
class Zefram_Application_Resource_Config
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @return Zend_Config
     */
    public function init()
    {
        $config = new Zend_Config(array(), true);

        foreach ($this->getOptions() as $file) {
            if (is_string($file)) {
                $config->merge(Zefram_Config::factory($file));
            }
        }

        $config->setReadOnly();
        return $config;
    }
}
