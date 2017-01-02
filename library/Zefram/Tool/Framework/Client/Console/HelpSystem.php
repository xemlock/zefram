<?php

class Zefram_Tool_Framework_Client_Console_HelpSystem extends Zend_Tool_Framework_Client_Console_HelpSystem
{
    /**
     * Header to be shown by the help system
     *
     * @var bool|string|array
     */
    protected $_header = true;

    /**
     * Set header to be displayed by the help system.
     *
     * When header is specified as an array, each line of header may be
     * also an array, whose first item will be treated as the content,
     * and the next item as an array of decorator settings.
     *
     * E.g., the default decorator can be provided as:
     *
     * <pre>
     *   array(
     *       array('Zend Framework', array('color' => array('hiWhite'), 'separator' => false))),
     *       ' Command Line Console Tool v' . Zend_Version::VERSION . '',
     *   )
     * </pre>
     *
     * @param bool|string|array $header     Set TRUE to show the default Zend Tool header,
     *                                      FALSE to disable header, a string or an array
     *                                      of strings for custom header
     * @return Zefram_Tool_Framework_Client_Console_HelpSystem
     * @throws Zend_Tool_Framework_Client_Exception
     */
    public function setHeader($header)
    {
        if (!is_bool($header) && !is_string($header) && !is_array($header)) {
            throw new Zend_Tool_Framework_Client_Exception(sprintf(
                'Header must be a boolean, string or an array. %s was provided instead',
                is_object($header) ? get_class($header) : gettype($header)
            ));
        }
        $this->_header = $header;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function _respondWithHeader()
    {
        if (is_bool($this->_header)) {
            if ($this->_header) {
                parent::_respondWithHeader();
            }
            return $this;
        }

        foreach ((array) $this->_header as $content) {
            if (is_array($content)) {
                $contentSpec = $content;
                $content = array_shift($contentSpec);
                $decoratorOptions = array_shift($contentSpec);
            } else {
                $decoratorOptions = null;
            }
            $this->_response->appendContent((string) $content, (array) $decoratorOptions);
        }

        return $this;
    }
}
