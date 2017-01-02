<?php

class Zefram_Tool_Framework_Client_Console_ArgumentParser
    extends Zend_Tool_Framework_Client_Console_ArgumentParser
{
    /**
     * @var Zend_Tool_Framework_Client_Console_HelpSystem
     */
    protected $_helpSystem;

    /**
     * Sets a help system instance
     *
     * @param Zend_Tool_Framework_Client_Console_HelpSystem $helpSystem
     * @return Zefram_Tool_Framework_Client_Console_ArgumentParser
     */
    public function setHelpSystem(Zend_Tool_Framework_Client_Console_HelpSystem $helpSystem = null)
    {
        $this->_helpSystem = $helpSystem;
        return $this;
    }

    /**
     * Retrieves a help system or initializes a default one
     *
     * @return Zend_Tool_Framework_Client_Console_HelpSystem
     */
    public function getHelpSystem()
    {
        if (!$this->_helpSystem instanceof Zend_Tool_Framework_Client_Console_HelpSystem) {
            $this->_helpSystem = new Zend_Tool_Framework_Client_Console_HelpSystem();
        }
        return $this->_helpSystem;
    }

    /**
     * {@inheritDoc}
     */
    protected function _createHelpResponse($options = array())
    {
        $helpSystem = $this->getHelpSystem();
        $helpSystem->setRegistry($this->_registry);

        if (isset($options['error'])) {
            $helpSystem->respondWithErrorMessage($options['error']);
        }

        if (isset($options['actionName']) && isset($options['providerName'])) {
            $helpSystem->respondWithSpecialtyAndParamHelp($options['providerName'], $options['actionName']);
        } elseif (isset($options['actionName'])) {
            $helpSystem->respondWithActionHelp($options['actionName']);
        } elseif (isset($options['providerName'])) {
            $helpSystem->respondWithProviderHelp($options['providerName']);
        } else {
            $helpSystem->respondWithGeneralHelp();
        }
    }
}
