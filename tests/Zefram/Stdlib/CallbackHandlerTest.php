<?php

class Zefram_Stdlib_CallbackHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $_args;

    public function setUp()
    {
        if (isset($this->_args)) {
            unset($this->_args);
        }
    }

    public function testsCallbackShouldStoreArgs()
    {
        $handler = new Zefram_Stdlib_CallbackHandler('rand', array(), array('baz'));
        $this->assertSame(array('baz'), $handler->getArgs());
    }

    public function testCallbackShouldStoreMetadataAndArgs()
    {
        $handler = new Zefram_Stdlib_CallbackHandler('rand', array('foo' => 'bar'), array('baz'));
        $this->assertSame(array('foo' => 'bar'), $handler->getMetadata());
        $this->assertSame(array('baz'), $handler->getArgs());
    }

    public function testCallbackShouldStoreArgsIfArgsIsEmptyAndMetadataIsAList()
    {
        $handler = new Zefram_Stdlib_CallbackHandler('rand', array('foo', 'bar', 'baz'));
        $this->assertSame(array(), $handler->getMetadata());
        $this->assertSame(array('foo', 'bar', 'baz'), $handler->getArgs());
    }

    public function testDerivedCallbackShouldCopyMetadataAndArgsFromBaseCallback()
    {
        $base = new Zefram_Stdlib_CallbackHandler('rand', array('foo' => 'bar'), array('baz'));
        $derived = new Zefram_Stdlib_CallbackHandler($base);
        $this->assertSame(array('foo' => 'bar'), $derived->getMetadata());
        $this->assertSame(array('baz'), $derived->getArgs());
    }

    public function testDerivedCallbackShouldNotUseMetadataFromBaseCallbackIfMetadataProvided()
    {
        $base = new Zefram_Stdlib_CallbackHandler('rand', array('foo' => 'bar'));
        $derived = new Zefram_Stdlib_CallbackHandler($base, array('baz' => 'qux'));
        $this->assertSame(array('baz' => 'qux'), $derived->getMetadata());
    }

    public function testDerivedCallbackShouldNotUseArgsFromBaseCallbackIfArgsProvided()
    {
        $base = new Zefram_Stdlib_CallbackHandler('rand', array('foo', 'bar'));
        $derived = new Zefram_Stdlib_CallbackHandler($base, array('baz', 'qux'));
        $this->assertSame(array('baz', 'qux'), $derived->getArgs());
    }

    public function testCallbackShouldBeArrayIfContainsDoubleColon()
    {
        $handler = new Zefram_Stdlib_CallbackHandler('Zefram_Stdlib_CallbackHandlerTest::staticCall');
        $this->assertSame(array('Zefram_Stdlib_CallbackHandlerTest', 'staticCall'), $handler->getCallback());
    }

    public function testCallbackShouldCallInvokeOnObjectIfPresent()
    {
        $object = new Zefram_Stdlib_TestAsset_InvokableObject();
        $handler = new Zefram_Stdlib_CallbackHandler($object);

        $this->assertEquals($object->__invoke(), $handler->call());
    }

    public function testCallShouldInvokeCallbackWithArgs()
    {
        $handler = new Zefram_Stdlib_CallbackHandler(array($this, 'handleCall'), array('foo', 'bar', 'baz'));
        $handler->call(array('qux'));
        $this->assertSame(array('foo', 'bar', 'baz', 'qux'), $this->_args);
    }

    public function testInvokeCallback()
    {
        $handler = new Zefram_Stdlib_CallbackHandler(array($this, 'handleCall'));
        $handler->invoke('foo', 'bar');
        $this->assertSame(array('foo', 'bar'), $this->_args);
    }

    public function handleCall()
    {
        $this->_args = func_get_args();
    }

    public static function staticCall()
    {
        // empty function
    }
}
