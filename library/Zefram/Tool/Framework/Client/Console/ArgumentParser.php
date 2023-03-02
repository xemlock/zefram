<?php

/**
 * Additional features:
 * - Allows customizing help system instance
 * - Allows controlling how to behave when remaining arguments are detected
 * - Overrides an argument parsing bugs in
 *   {@link Zend_Tool_Framework_Client_Console_ArgumentParser::_parseProviderOptionsPart()}
 * - Supports actions without short params defined
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

        $this->_parentParseProviderOptionsPart();

        $this->_remainingArgs = $this->_argumentsWorking;

        // If remaining args are allowed, clear _argumentsWorking to let know
        // parse() that there are no arguments left and parsing was successful
        if (count($this->_argumentsWorking) && $this->_allowRemainingArgs) {
            $this->_argumentsWorking = array();
        }
    }

    /**
     * Reimplementation of {@link Zend_Tool_Framework_Client_Console_ArgumentParser::_parseProviderOptionsPart()}
     * that gracefully handles actions that have no short params defined.
     *
     * The original implementation hasn't changed since ZF 1.10.0alpha1 (2009-12-21).
     * The key difference is the added check for existence of
     * <code>$paramNameShortValues[$parameterNameLong]</code> before adding it to
     * getopt options.
     *
     * Also this implementation correctly handles situation when params metadata
     * was not found. This fixes Uncaught Error when trying to run invalid action
     * on a valid provider.
     *
     * @return void
     */
    protected function _parentParseProviderOptionsPart()
    {
        if (current($this->_argumentsWorking) == '?') {
            $this->_help = true;
            return;
        }

        $searchParams = array(
            'type'          => 'Tool',
            'providerName'  => $this->_request->getProviderName(),
            'actionName'    => $this->_request->getActionName(),
            'specialtyName' => $this->_request->getSpecialtyName(),
            'clientName'    => 'console'
        );

        $actionableMethodLongParamsMetadata = $this->_manifestRepository->getMetadata(
            array_merge($searchParams, array('name' => 'actionableMethodLongParams'))
        );

        if (!$actionableMethodLongParamsMetadata) {
            // missing params metadata
            return;
        }

        $actionableMethodShortParamsMetadata = $this->_manifestRepository->getMetadata(
            array_merge($searchParams, array('name' => 'actionableMethodShortParams'))
        );

        if ($actionableMethodShortParamsMetadata) {
            $paramNameShortValues = $actionableMethodShortParamsMetadata->getValue();
        } else {
            $paramNameShortValues = array();
        }

        $getoptOptions = array();
        $wordArguments = array();
        $longParamCanonicalNames = array();

        $actionableMethodLongParamsMetadataReference = $actionableMethodLongParamsMetadata->getReference();
        foreach ($actionableMethodLongParamsMetadata->getValue() as $parameterNameLong => $consoleParameterNameLong) {
            $optionConfig = $consoleParameterNameLong;

            $parameterInfo = $actionableMethodLongParamsMetadataReference['parameterInfo'][$parameterNameLong];

            if (isset($paramNameShortValues[$parameterNameLong])) {
                $optionConfig .= '|';

                // process ParameterInfo into array for command line option matching
                if ($parameterInfo['type'] == 'string' || $parameterInfo['type'] == 'bool') {
                    $optionConfig .= $paramNameShortValues[$parameterNameLong]
                        . (($parameterInfo['optional']) ? '-' : '=') . 's';
                } elseif (in_array($parameterInfo['type'], array('int', 'integer', 'float'))) {
                    $optionConfig .= $paramNameShortValues[$parameterNameLong]
                        . (($parameterInfo['optional']) ? '-' : '=') . 'i';
                } else {
                    $optionConfig .= $paramNameShortValues[$parameterNameLong] . '-s';
                }
            }

            $getoptOptions[$optionConfig] = ($parameterInfo['description'] != '') ? $parameterInfo['description'] : 'No description available.';


            // process ParameterInfo into array for command line WORD (argument) matching
            $wordArguments[$parameterInfo['position']]['parameterName'] = $parameterInfo['name'];
            $wordArguments[$parameterInfo['position']]['optional']      = $parameterInfo['optional'];
            $wordArguments[$parameterInfo['position']]['type']          = $parameterInfo['type'];

            // keep a translation of console to canonical names
            $longParamCanonicalNames[$consoleParameterNameLong] = $parameterNameLong;
        }


        if (!$getoptOptions) {
            // no options to parse here, return
            return;
        }

        // if non-option arguments exist, attempt to process them before processing options
        $wordStack = array();
        while (($wordOnTop = array_shift($this->_argumentsWorking))) {
            if (substr($wordOnTop, 0, 1) != '-') {
                array_push($wordStack, $wordOnTop);
            } else {
                // put word back on stack and move on
                array_unshift($this->_argumentsWorking, $wordOnTop);
                break;
            }

            if (count($wordStack) == count($wordArguments)) {
                // when we get at most the number of arguments we are expecting
                // then break out.
                break;
            }

        }

        if ($wordStack && $wordArguments) {
            for ($wordIndex = 1; $wordIndex <= count($wordArguments); $wordIndex++) {
                if (!array_key_exists($wordIndex-1, $wordStack) || !array_key_exists($wordIndex, $wordArguments)) {
                    break;
                }
                $this->_request->setProviderParameter($wordArguments[$wordIndex]['parameterName'], $wordStack[$wordIndex-1]);
                unset($wordStack[$wordIndex-1]);
            }
        }

        $getoptParser = new Zend_Console_Getopt($getoptOptions, $this->_argumentsWorking, array('parseAll' => false));
        $getoptParser->parse();
        foreach ($getoptParser->getOptions() as $option) {
            $value = $getoptParser->getOption($option);
            $providerParamOption = $longParamCanonicalNames[$option];
            $this->_request->setProviderParameter($providerParamOption, $value);
        }

        $this->_argumentsWorking = $getoptParser->getRemainingArgs();

        return;
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
