<?php

class Zefram_Tool_Framework_Client_Console_ResponseDecorator_CommandName
    implements Zend_Tool_Framework_Client_Response_ContentDecorator_Interface
{
    public function getName()
    {
        return 'commandName';
    }

    /**
     * Replaces all occurrences of 'zf' in the input with a %commandName%
     * placeholder, and then all occurrences of the placeholder with the
     * given command name.
     *
     * This is designed to override 'zf' command name hardcoded in
     * {@link Zend_Tool_Framework_Client_Console_HelpSystem}.
     *
     * @param string $content
     * @param string $commandName
     * @return string
     */
    public function decorate($content, $commandName)
    {
        if (strlen($commandName)) {
            if ($commandName !== 'zf') {
                $content = preg_replace('/(?:\b)(zf)(?:\b)/', '%commandName%', $content);
            }
            $content = str_replace('%commandName%', $commandName, $content);
        }
        return $content;
    }
}
