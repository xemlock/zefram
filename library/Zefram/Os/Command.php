<?php

class Zefram_Os_Command
{
    /**
     * @var string
     */
    protected $_exec;

    /**
     * @var args
     */
    protected $_args;

    /**
     * @var array
     */
    protected $_streamRedirections;

    public function __construct($exec, $args = null, $streamRedirections = null)
    {
        $this->_exec = (string) $exec;

        if (is_array($args)) {
            $this->setArgs($args);
        }

        if (is_array($streamRedirections)) {
            $this->setStreamRedirections($streamRedirections);
        }
    }

    public function setArgs(array $args)
    {
        $this->_args = $args;
        return $this;
    }

    public function setStreamRedirections(array $streamRedirections)
    {
        $this->_streamRedirections = array();

        foreach ($streamRedirections as $stream => $target) {
            switch ($stream) {
                // stdin can be opened only in read-only mode
                case 'php://stdin':
                case 'stdin':
                    $redir = '<';
                    break;

                case 'php://stdout':
                case 'stdout':
                    $redir = '1>';
                    break;

                case 'php://stderr':
                case 'stderr':
                    $redir = '2>';
                    break;

                default:
                    throw new InvalidArgumentException(sprintf(
                        'Invalid output stream: %s', $stream
                    ));
            }

            if (null === $target) {
                $target = Zefram_Os::isWindows() ? 'nul' : '/dev/null';
            } else {
                switch ($target) {    
                    case 'php://stdin':
                    case 'stdin':
                        $target = '&0';
                        break;

                    case 'php://stdout':
                    case 'stdout':
                        $target = '&1';
                        break;

                    case 'php://stderr':
                    case 'stderr':
                        $target = '&2';
                        break;

                    case '/dev/null':
                        if (Zefram_Os::isWindows()) {
                            $target = 'nul';
                        }
                        break;

                    default:
                        $target = escapeshellarg($target);
                        break;
                }
            }

            $this->_streamRedirections[]
        }
    }

    public static function factory($exec, $args = null, $stream_redirections)
    {
        return new self($exec, $args, $stream_redirections);
    }
}
