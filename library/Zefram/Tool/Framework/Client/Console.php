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
     * @return Zefram_Tool_Framework_Client_Console
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
     * @return Zefram_Tool_Framework_Client_Console
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
     * @return Zefram_Tool_Framework_Client_Console
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
     * @return Zefram_Tool_Framework_Client_Console
     */
    public function setExitCode($exitCode)
    {
        $this->_exitCode = (int) $exitCode;
        return $this;
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

        $optParser = new Zefram_Tool_Framework_Client_Console_ArgumentParser();
        $optParser->setHelpSystem($this->_createHelpSystem());
        $optParser->setArguments($_SERVER['argv'])
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

    /**
     * @return Zefram_Tool_Framework_Client_Console_HelpSystem
     */
    protected function _createHelpSystem()
    {
        $helpSystem = new Zefram_Tool_Framework_Client_Console_HelpSystem();
        $helpSystem->setHeader($this->getHelpHeader());

        return $helpSystem;
    }
}
