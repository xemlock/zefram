<?php

/**
 * A simple wrapper for proc_open function for running processes, based on
 * http://omegadelta.net/2012/02/08/stdin-stdout-stderr-with-proc_open-in-php/
 */
class Zefram_System_Process
{
    const CHUNK_SIZE = 8192;

    const SELECT_TIMEOUT = 200000;

    /**
     * @var string
     */
    protected $_command;

    /**
     * @var array
     */
    protected $_args;

    /**
     * @var bool
     */
    protected $_isRunning = false;

    /**
     * @var string
     */
    protected $_input;

    /**
     * @var string
     */
    protected $_output;

    /**
     * @var string
     */
    protected $_error;

    /**
     * @var int
     */
    protected $_exitCode;

    /**
     * @param string|array $command
     */
    public function __construct($command)
    {
        if (is_array($command)) {
            $this->_command = array_shift($command);
            $this->_args = $command;
        } else {
            $this->_command = $command;
        }
    }

    /**
     * Set contents of input stream
     *
     * @param string $input
     * @return Zefram_System_Process This process instance
     * @throws Zefram_System_Exception
     */
    public function setInput($input)
    {
        if ($this->_isRunning) {
            throw new Zefram_System_Exception('You cannot modify input of an already running process');
        }

        $this->_input = (string) $input;
        return $this;
    }

    /**
     * Returns contents of output stream
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->_output;
    }

    /**
     * Returns contents of error stream
     *
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Returns the termination status of the process that was run
     *
     * @return int
     */
    public function getExitCode()
    {
        return $this->_exitCode;
    }

    /**
     * Runs the process and waits for it to terminate
     *
     * @return Zefram_System_Process
     * @throws Zefram_System_Exception
     */
    public function run()
    {
        if ($this->_isRunning) {
            throw new Zefram_System_Exception('run() cannot be called on an already running process');
        }

        $command[] = escapeshellcmd($this->_command);
        foreach ($this->_args as $arg) {
            $command[] = escapeshellarg($arg);
        }
        $command = implode(' ', $command);

        $descriptorSpec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        $cwd = null;
        $env = null;

        $process = proc_open($command, $descriptorSpec, $pipes, $cwd, $env);

        if (!is_resource($process)) {
            throw new Zefram_System_Exception('Unable to run process');
        }

        $this->_output = '';
        $this->_error = '';

        $inputOffset = 0;
        $inputLength = strlen($this->_input);

        $inputDone = false;
        $outputDone = false;
        $errorDone = false;

        // make io streams non blocking
        stream_set_blocking($pipes[0], 0);
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);

        while (true) {
            $readPipes = array();
            if (!$outputDone) {
                $readPipes[] = $pipes[1];
            }
            if (!$errorDone) {
                $readPipes[] = $pipes[2];
            }

            $writePipes = array();
            if (!$inputDone) {
                $writePipes[] = $pipes[0];
            }

            $exceptPipes = array();

            // PHP will complain with "Warning: stream_select(): No stream arrays were passed in ****"
            // if all arrays are empty or null, and it WON'T sleep, it will return immediately.
            // If no pipes are available this means that both input and output has ended.
            if (!$readPipes && !$writePipes) {
                break;
            }

            // Block until io becomes possible
            stream_select($readPipes, $writePipes, $exceptPipes, 0, self::SELECT_TIMEOUT);

            // Write data to stdin
            if (!empty($writePipes)) {
                $inputBytes = fwrite($pipes[0], substr($this->_input, $inputOffset, self::CHUNK_SIZE));
                if ($inputBytes !== false) {
                    $inputOffset += $inputBytes;
                }
                if ($inputOffset >= $inputLength) {
                    $inputDone = true;
                    fclose($pipes[0]);
                }
            }

            // Read data from stdout and stderr
            foreach ($readPipes as $pipe) {
                if ($pipe === $pipes[1]) {
                    $this->_output .= fread($pipes[1], self::CHUNK_SIZE);
                    if (feof($pipes[1])) {
                        fclose($pipes[1]);
                        $outputDone = true;
                    }
                } elseif ($pipe === $pipes[2]) {
                    $this->_error .= fread($pipes[2], self::CHUNK_SIZE);
                    if (feof($pipes[2])) {
                        fclose($pipes[2]);
                        $errorDone = true;
                    }
                }
            }

            if ($inputDone && $outputDone && $errorDone) {
                break;
            }
        }

        $this->_exitCode = proc_close($process);
        $this->_isRunning = false;

        return $this;
    }
}
