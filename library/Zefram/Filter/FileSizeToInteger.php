<?php

class Zefram_Filter_FileSizeToInteger implements Zend_Filter_Interface
{
    /**
     * @var bool
     */
    protected $_binary = true;

    /**
     * @param array|Zend_Config $options
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            if (is_object($options) && method_exists($options, 'toArray')) {
                $options = $options->toArray();
            }

            $options = (array) $options;

            if (isset($options['binary'])) {
                $this->setBinary($options['binary']);
            }
        }
    }

    /**
     * @param  bool $binary
     * @return Zefram_Filter_FileSizeToInteger
     */
    public function setBinary($binary)
    {
        $this->_binary = (bool) $binary;
        return $this;
    }

    /**
     * @return bool
     */
    public function getBinary()
    {
        return $this->_binary;
    }

    /**
     * @param mixed $fileSize
     * @param bool $binary
     * @return int
     */
    public function filter($fileSize, $binary = null)
    {
        $binary = null === $binary ? $this->_binary : $binary;
        return self::filterStatic($fileSize, $binary);
    }

    /**
     * @param  string $fileSize
     * @param  bool $binary
     * @return int
     */
    public static function filterStatic($fileSize, $binary = true)
    {
        if (!is_int($fileSize)) {
            // trim whitespaces and units
            $fileSize = trim($fileSize, " \t\n\rBb");

            if (!is_numeric($fileSize)) {
                $suffix = strtoupper(substr($fileSize, -1));
                $fileSize = intval(substr($fileSize, 0, -1));

                $multiplier = $binary ? 1024 : 1000;

                switch ($suffix) {
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 'Y':
                        $fileSize *= $multiplier;

                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 'Z':
                        $fileSize *= $multiplier;

                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 'E':
                        $fileSize *= $multiplier;

                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 'P':
                        $fileSize *= $multiplier;

                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 'T':
                        $fileSize *= $multiplier;

                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 'G':
                        $fileSize *= $multiplier;

                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 'M' :
                        $fileSize *= $multiplier;

                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 'K' :
                        $fileSize *= $multiplier;
                }
            }
        }

        return intval($fileSize);
    }
}
