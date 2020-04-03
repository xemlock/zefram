<?php

class Zefram_Validate_EmailAddressTest extends PHPUnit_Framework_TestCase
{
    public function testHostnameValidator()
    {
        $validator = new Zefram_Validate_EmailAddress();
        $this->assertInstanceOf('Zefram_Validate_Hostname', $validator->getHostnameValidator());
    }
}
