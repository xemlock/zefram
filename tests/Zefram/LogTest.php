<?php

class Zefram_LogTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterErrorHandler()
    {
        $stream = fopen('php://memory', 'w');
        $config = array(
            'memory' => array(
                'writerName'      => 'Stream',
                'writerNamespace' => 'Zend_Log_Writer',
                'writerParams'    => array(
                    'stream'      => $stream,
                ),
            ),
            'registerErrorHandler' => true,
        );

        set_error_handler(function () {}); // Suppress phpunit error handler
        $logger = Zefram_Log::factory($config);

        trigger_error('Notice triggered in test', E_USER_NOTICE);

        restore_error_handler(); // Pop off noop handler used for supressing phpunit's handler
        restore_error_handler(); // Pop off $logger error handler

        rewind($stream);
        $this->assertContains('Notice triggered in test', stream_get_contents($stream));
    }
}
