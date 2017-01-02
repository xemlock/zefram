<?php

class Zefram_Tool_Framework_Client_Console_ResponseDecorator_CommandName
    implements Zend_Tool_Framework_Client_Response_ContentDecorator_Interface
{
    public function getName()
    {
        return 'commandName';
    }

    /**
     * Replaces all occurrences of 'zf' in the input with a given command name.
     *
     * @param string $content
     * @param string $commandName
     * @return string
     */
    public function decorate($content, $commandName)
    {
        if (strlen($commandName)) {
            $content = preg_replace('/(\s*)(zf)(\s*)/', '$1' . $commandName . '$3', $content);
        }
        return $content;
    }
}
