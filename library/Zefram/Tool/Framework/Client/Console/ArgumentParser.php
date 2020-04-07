<?php

/**
 * Additional features:
 * - Allows customizing help system instance
 * - Allows controlling how to behave when remaining arguments are detected
 * - Overrides an argument parsing bug in
 *   {@link Zend_Tool_Framework_Client_Console_ArgumentParser::_parseProviderOptionsPart()}
 *
 * Zend implementation reports failure when there are remaining arguments,
 * which affects usefulness of the parser in more general use cases.
 */
class Zefram_Tool_Framework_Client_Console_ArgumentParser
    extends Zend_Tool_Framework_Client_Console_ArgumentParser
{
    /**
     * Whether to allow remaining args when parsing arguments
     * @var bool
     */
    protected $_allowRemainingArgs = false;

    /**
     * Argument left over after parsing action and provider
     * @param array
     */
    protected $_remainingArgs = array();

    /**
     * @var Zend_Tool_Framework_Client_Console_HelpSystem
     */
    protected $_helpSystem;

    /**
     * Set arguments for parsing
     *
     * @param array $arguments
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        parent::setArguments($arguments);
        $this->_remainingArgs = array();
        return $this;
    }

    /**
     * @param bool $allowRemainingArgs
     * @return $this
     */
    public function setAllowRemainingArgs($allowRemainingArgs)
    {
        $this->_allowRemainingArgs = (bool) $allowRemainingArgs;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowRemainingArgs()
    {
        return $this->_allowRemainingArgs;
    }

    /**
     * @return array
     */
    public function getRemainingArgs()
    {
        return $this->_remainingArgs;
    }

    /**
     * Sets a help system instance
     *
     * @param Zend_Tool_Framework_Client_Console_HelpSystem $helpSystem
     * @return $this
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
     * Internal routine for parsing the provider options from the command line
     *
     * It's also used for handling arguments that remained after parsing,
     * depending on 'allowRemainingArgs' flag.
     *
     * @return void
     */
    protected function _parseProviderOptionsPart()
    {
        if (current($this->_argumentsWorking) == '?') {
            $this->_help = true;
            $this->_remainingArgs = array_slice($this->_argumentsWorking, 1);
            return;
        }

        // There is a bug in Zend_Tool_Framework_Client_Console_ArgumentParser::_parseProviderOptionsPart()
        // that throws Uncaught Error: Call to a member function getValue() on null
        // when there is an argument after provider given as '?', e.g.:
        // zf show ? version
        if ($this->_help) {
            $this->_remainingArgs = $this->_argumentsWorking;
            return;
        }

        parent::_parseProviderOptionsPart();

        $this->_remainingArgs = $this->_argumentsWorking;

        // If remaining args are allowed, clear _argumentsWorking to let know
        // parse() that there are no arguments left and parsing was successful
        if (count($this->_argumentsWorking) && $this->_allowRemainingArgs) {
            $this->_argumentsWorking = array();
        }
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
