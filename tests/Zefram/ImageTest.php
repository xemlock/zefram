<?php

class Zefram_ImageTest extends PHPUnit_Framework_TestCase
{
    function testCreateFromPath()
    {
        $path = dirname(__FILE__) . '/../resources/ZendFramework-logo.png';
        $image = new Zefram_Image($path);

        $this->assertEquals(Zefram_Image::PNG, $image->getType());
        $this->assertEquals(2539, $image->getWidth());
        $this->assertEquals(672, $image->getHeight());

        return $image;
    }

    function testCreateFromResource()
    {
        $path = dirname(__FILE__) . '/../resources/ZendFramework-logo.png';
        $resource = imagecreatefrompng($path);

        $image = new Zefram_Image($resource);

        $this->assertNull($image->getType());
        $this->assertEquals(2539, $image->getWidth());
        $this->assertEquals(672, $image->getHeight());
    }

    /**
     * @depends testCreateFromPath
     */
    function testCreateFromInstance(Zefram_Image $other)
    {
        $image = new Zefram_Image($other);

        $this->assertEquals(Zefram_Image::PNG, $image->getType());
        $this->assertEquals(2539, $image->getWidth());
        $this->assertEquals(672, $image->getHeight());
    }
}
