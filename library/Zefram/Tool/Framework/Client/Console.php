<?php

class Zefram_Tool_Framework_Client_Console extends Zend_Tool_Framework_Client_Console
{
    /**
     * @var string
     */
    protected $_name = 'console';

    /**
     * @var string
     */
    protected $_commandName = 'zf';

    /**
     * @var string
     */
    protected $_helpHeader;

    /**
     * @var string
     */
    protected $_prompt;

    /**
     * @var int
     */
    protected $_exitCode = 0;

    /**
     * Whether to allow remaining args when parsing arguments
     *
     * See {@link Zefram_Tool_Framework_Client_Console_ArgumentParser::setAllowRemainingArgs()}
     * for details.
     *
     * @var boolean
     */
    protected $_allowRemainingArgs = false;

    /**
     * @var Zefram_Tool_Framework_Client_Console_ArgumentParser
     */
    protected $_argumentParser;

    /**
     * This is typically called as a main function of a cli script.
     *
     * @param array $options
     * @return int
     */
    public static function main($options = array())
    {
        $cliClient = new self($options);
        $cliClient->dispatch();

        return $cliClient->getExitCode();
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $commandName
     * @return $this
     */
    public function setCommandName($commandName)
    {
        $this->_commandName = $commandName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommandName()
    {
        return $this->_commandName;
    }

    /**
     * Set response header
     *
     * @param mixed $helpHeader See {@link Zefram_Tool_Framework_Client_Console_HelpSystem::setHeader()}
     *                          for details
     * @return $this
     */
    public function setHelpHeader($helpHeader)
    {
        $this->_helpHeader = $helpHeader;
        return $this;
    }

    /**
     * Retrieve respose header
     *
     * @return string
     */
    public function getHelpHeader()
    {
        return $this->_helpHeader;
    }

    /**
     * @return string
     */
    public function getPrompt()
    {
        if ($this->_prompt) {
            return $this->_prompt;
        }
        return $this->getCommandName() . '> ';
    }

    /**
     * @param $prompt
     * @return Zefram_Tool_Framework_Client_Console
     */
    public function setPrompt($prompt)
    {
        $this->_prompt = (string) $prompt;
        return $this;
    }

    /**
     * @return int
     */
    public function getExitCode()
    {
        return $this->_exitCode;
    }

    /**
     * @param int $exitCode
     * @return $this
     */
    public function setExitCode($exitCode)
    {
        $this->_exitCode = (int) $exitCode;
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
     * @param bool $allowRemainingArgs
     * @return $this
     */
    public function setAllowRemainingArgs($allowRemainingArgs)
    {
        $this->_allowRemainingArgs = (bool) $allowRemainingArgs;
        return $this;
    }

    /**
     * @return Zefram_Tool_Framework_Client_Console_ArgumentParser
     */
    public function getArgumentParser()
    {
        if (!$this->_argumentParser) {
            $this->_argumentParser = new Zefram_Tool_Framework_Client_Console_ArgumentParser();
        }
        return $this->_argumentParser;
    }

    /**
     * {@inheritDoc}
     */
    public function handleInteractiveInputRequest(Zend_Tool_Framework_Client_Interactive_InputRequest $inputRequest)
    {
        fwrite(STDOUT, $inputRequest->getContent() . PHP_EOL . $this->getPrompt());
        $inputContent = fgets(STDIN);
        return rtrim($inputContent); // remove the return from the end of the string
    }

    /**
     * {@inheritDoc}
     */
    protected function _preDispatch()
    {
        $response = $this->_registry->getResponse();

        $response->addContentDecorator(new Zefram_Tool_Framework_Client_Console_ResponseDecorator_CommandName());
        $response->addContentDecorator(new Zend_Tool_Framework_Client_Console_ResponseDecorator_AlignCenter());
        $response->addContentDecorator(new Zend_Tool_Framework_Client_Console_ResponseDecorator_Indention());
        $response->addContentDecorator(new Zend_Tool_Framework_Client_Console_ResponseDecorator_Blockize());

        if (function_exists('posix_isatty')) {
            $response->addContentDecorator(new Zend_Tool_Framework_Client_Console_ResponseDecorator_Colorizer());
        }

        $response->addContentDecorator(new Zend_Tool_Framework_Client_Response_ContentDecorator_Separator());
        $response->setDefaultDecoratorOptions(array('separator' => true, 'commandName' => $this->getCommandName()));

        $this->getArgumentParser()
            ->setHelpSystem($this->_createHelpSystem())
            ->setArguments($_SERVER['argv'])
            ->setAllowRemainingArgs($this->_allowRemainingArgs)
            ->setRegistry($this->_registry)
            ->parse();
    }

    /**
     * {@inheritDoc}
     */
    protected function _postDispatch()
    {
        $request = $this->_registry->getRequest();
        $response = $this->_registry->getResponse();

        if ($response->isException()) {
            if (!$this->getExitCode()) {
                $this->setExitCode(1);
            }

            $helpSystem = $this->_createHelpSystem();
            $helpSystem->setRegistry($this->_registry)
                ->respondWithErrorMessage($response->getException()->getMessage(), $response->getException())
                ->respondWithSpecialtyAndParamHelp(
                    $request->getProviderName(),
                    $request->getActionName()
                );
        }

        echo PHP_EOL;
    }

    protected function _preInit()
    {
        parent::_preInit();

        /** @var Zend_Tool_Framework_Loader_BasicLoader $loader */
        $loader = $this->_registry->getLoader();

        // Access protected property $_classesToLoad of loader object
        // https://ocramius.github.io/blog/fast-php-object-to-array-conversion/
        $loaderAsArray = (array) $loader;
        $classesToLoad = $loaderAsArray["\0*\0" . '_classesToLoad'];

        foreach ($classesToLoad as $key => $value) {
            if ($value === 'Zend_Tool_Framework_Client_Console_Manifest') {
                $classesToLoad[$key] = 'Zefram_Tool_Framework_Client_Console_Manifest';
            }
        }

        $loader->setClassesToLoad($classesToLoad);
    }

    /**
     * @return Zefram_Tool_Framework_Client_Console_HelpSystem
     */
    protected function _createHelpSystem()
    {
        $helpSystem = new Zefram_Tool_Framework_Client_Console_HelpSystem();
        $helpSystem->setHeader($this->getHelpHeader());

        return $helpSystem;
    }

    /**
     * Initialize the client for use
     *
     * This is an almost exact copy of {@link Zend_Tool_Framework_Client_Console::initialize()}
     * but it uses an instance of {@link Zefram_Tool_Framework_Client_Manifest} as the base
     * manifest, because the original one is incapable of dealing with parameter names starting
     * with the same letter.
     */
    public function initialize()
    {
        // if its already initialized, no need to initialize again
        if ($this->_isInitialized) {
            return;
        }

        // run any preInit
        $this->_preInit();

        $manifest = $this->_registry->getManifestRepository();
        $manifest->addManifest(new Zefram_Tool_Framework_Client_Manifest());

        // setup the debug log
        if (!$this->_debugLogger instanceof Zend_Log) {
            $this->_debugLogger = new Zend_Log(new Zend_Log_Writer_Null());
        }

        // let the loader load, then the repositories process whats been loaded
        $this->_registry->getLoader()->load();

        // process the action repository
        $this->_registry->getActionRepository()->process();

        // process the provider repository
        $this->_registry->getProviderRepository()->process();

        // process the manifest repository
        $this->_registry->getManifestRepository()->process();

        if ($this instanceof Zend_Tool_Framework_Client_Interactive_OutputInterface) {
            $this->_registry->getResponse()->setContentCallback(array($this, 'handleInteractiveOutput'));
        }
    }
}
