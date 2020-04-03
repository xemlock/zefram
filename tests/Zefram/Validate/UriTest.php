<?php

class Zefram_Validate_UriTest extends PHPUnit_Framework_TestCase
{
    public function testDefaultSettings()
    {
        $validator = new Zefram_Validate_Uri();

        $this->assertTrue($validator->isValid('http://example.com/path/to/file'));
        $this->assertTrue($validator->isValid('https://example.com/path/to/file'));
        $this->assertFalse($validator->isValid('ftp://example.com/path/to/file'));
    }

    public function testSchemeOption()
    {
        $validator = new Zefram_Validate_Uri(array('scheme' => 'scheme1'));
        $this->assertEquals('scheme1', $validator->getScheme());

        $validator->setOptions(array('scheme' => 'scheme2'));
        $this->assertEquals('scheme2', $validator->getScheme());

        $validator->setScheme('scheme3');
        $this->assertEquals('scheme3', $validator->getScheme());
    }

    public function testAllowOption()
    {
        $validator = new Zefram_Validate_Uri(array('allow' => Zend_Validate_Hostname::ALLOW_DNS));
        $this->assertEquals(Zend_Validate_Hostname::ALLOW_DNS, $validator->getAllow());
        $this->assertEquals(Zend_Validate_Hostname::ALLOW_DNS, $validator->getHostnameValidator()->getAllow());

        $validator->setAllow(Zend_Validate_Hostname::ALLOW_LOCAL);
        $this->assertEquals(Zend_Validate_Hostname::ALLOW_LOCAL, $validator->getAllow());
    }

    public function testValidation()
    {
        $validator = new Zefram_Validate_Uri();
        $validator->setScheme('Zefram_Uri_Http');

        $validator->getHostnameValidator()->setAllow(Zend_Validate_Hostname::ALLOW_DNS);
        $this->assertTrue($validator->isValid('http://example.com/path/to/file'));
        $this->assertFalse($validator->isValid('http://192.168.1.1/path/to/file'));

        $validator->getHostnameValidator()->setAllow(Zend_Validate_Hostname::ALLOW_IP);
        $this->assertFalse($validator->isValid('http://example.com/path/to/file'));
        $this->assertTrue($validator->isValid('http://192.168.1.1/path/to/file'));

        $validator->setScheme('Zefram_Uri_File');
        $validator->getHostnameValidator()->setAllow(Zend_Validate_Hostname::ALLOW_URI);
        $this->assertTrue($validator->isValid('file:///path/to/file'));
        $this->assertTrue($validator->isValid('file://localhost/c:/WINDOWS/clock.avi'));

        $validator->getHostnameValidator()->setAllow(Zend_Validate_Hostname::ALLOW_DNS);
        // valid file url, host name validation skipped
        $this->assertTrue($validator->isValid('file:///path/to/file'));
        // valid file url, valid host name
        $this->assertTrue($validator->isValid('file://example.com/path/to/file'));
        // valid file url, but invalid host name
        $this->assertFalse($validator->isValid('file://192.168.1.1/path/to/file'));

        $this->assertFalse($validator->isValid('file://localhost/c|/WINDOWS/clock.avi'));

        $validator->getHostnameValidator()->setAllow(Zend_Validate_Hostname::ALLOW_LOCAL);
        $this->assertTrue($validator->isValid('file://localhost/c|/WINDOWS/clock.avi'));
        $this->assertTrue($validator->isValid('file:///c|/WINDOWS/clock.avi'));
        $this->assertTrue($validator->isValid('file://localhost/c:/WINDOWS/clock.avi'));
    }

    public function testHostnameValidator()
    {
        $validator = new Zefram_Validate_EmailAddress();
        $this->assertInstanceOf('Zefram_Validate_Hostname', $validator->getHostnameValidator());
    }
}
