<?php

require_once __DIR__ . '/../../../includes/classes/class.image.baseclass.php';
require_once __DIR__ . '/../../../vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs/vfsStream.php';
require_once __DIR__ . '/../../../lib/general.php';

class Unit_Image_ImageTest extends PHPUnit_Framework_TestCase
{
    private $_vfsRoot;

    public function setup() {
        $root = uniqid('vfsroot');
        org\bovigo\vfs\vfsStream::setup($root);
        $this->_vfsRoot = org\bovigo\vfs\vfsStream::url($root);
    }

    public function dataProviderResizeOffsetCalculations()
    {
        $inputs = array();

        // same size
        $inputs[] = array(10, 10, 10, 10, ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_CENTRE, 0, 0);

        // simple square sizes
        $inputs[] = array(10, 10, 20, 20, ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_NORTH | ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_EAST, 0, 0);
        $inputs[] = array(10, 10, 20, 20, ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_CENTRE, 5, 5);
        $inputs[] = array(10, 10, 20, 20, ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_SOUTH | ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_WEST, 10, 10);

        // oblong to square
        $inputs[] = array(20, 10, 30, 30, ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_NORTH | ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_EAST, 0, 0);
        $inputs[] = array(20, 10, 30, 30, ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_CENTRE, 5, 10);
        $inputs[] = array(20, 10, 30, 30, ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_SOUTH | ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_WEST, 10, 20);

        // square to oblong
        $inputs[] = array(10, 10, 30, 20, ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_NORTH | ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_EAST, 0, 0);
        $inputs[] = array(10, 10, 30, 20, ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_CENTRE, 10, 5);
        $inputs[] = array(10, 10, 30, 20, ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_SOUTH | ISC_IMAGE_BASECLASS::RESIZE_GRAVITY_WEST, 20, 10);

        return $inputs;
    }

    /**
     * @dataProvider dataProviderResizeOffsetCalculations
     * @param $oldWidth
     * @param $oldHeight
     * @param $newWidth
     * @param $newHeight
     * @param $gravity
     * @param $offsetX
     * @param $offsetY
     */
    public function testCalculateResizeOffsets($oldWidth, $oldHeight, $newWidth, $newHeight, $gravity, $offsetX, $offsetY)
    {
        $result = ISC_IMAGE_BASECLASS::calculateResizeOffsets($oldWidth, $oldHeight, $newWidth, $newHeight, $gravity);

        $expected = array(
            $offsetX,
            $offsetY,
        );

        $this->assertSame($expected, $result);
    }

    public function dataProviderHexRgb()
    {
        $inputs = array();

        $inputs[] = array('000000', 0, 0, 0);
        $inputs[] = array('FF0000', 255, 0, 0);
        $inputs[] = array('00FF00', 0, 255, 0);
        $inputs[] = array('0000FF', 0, 0, 255);
        $inputs[] = array('0FF000', 15, 240, 0);
        $inputs[] = array('000FF0', 0, 15, 240);

        $inputs[] = array('0000000', false);
        $inputs[] = array('00000', false);

        $inputs[] = array('ABCDEF', 171, 205, 239);

        // will be treated like 0BCDEF because hexdec ignores non-hex characters
        $inputs[] = array('BCDEFG', 11, 205, 239);

        return $inputs;
    }

    /**
     * @dataProvider dataProviderHexRgb
     * @param $hex
     * @param $red
     * @param $green
     * @param $blue
     */
    public function testHexRgb($hex, $red, $green = null, $blue = null)
    {
        $result = ISC_IMAGE_BASECLASS::hexRgb($hex);

        if ($red === false) {
            $expected = false;
        } else {
            $expected = array(
                $red,
                $green,
                $blue,
            );
        }

        $this->assertSame($expected, $result);
    }

    public function dataProviderConstrainedImageResize() {
        $data = array();
        $data[] = array(100, 200, 150, 150, 75, 150, true);
        $data[] = array(200, 200, 150, 150, 150, 150, true);
        $data[] = array(100, 100, 150, 150, 100, 100, false);
        $data[] = array(200, 100, 150, 150, 150, 75, true);
        return $data;
    }

    /**
     * @dataProvider dataProviderConstrainedImageResize
     * @param int $sourceW Width of the source image
     * @param int $souceH Height of the source image
     * @param int $constrainW Width of the source image
     * @param int $constrainH Height of the source image
     * @param int $resizedW Width of the source image
     * @param int $resizedH Height of the source image
     * @param bool $resize True when the file be resized
     */
    public function testConstrainedImageResize($sourceW, $sourceH, $constrainW, $constrainH, $resizedW, $resizedH, $resize) {
        $sourcePath = tempnam($this->_vfsRoot . DIRECTORY_SEPARATOR, 'src_');
        $destPath = tempnam($this->_vfsRoot . DIRECTORY_SEPARATOR, 'dest_');

        $writeOptions = new ISC_IMAGE_WRITEOPTIONS_GIF();

        $image = $this->getMock('MockImage', array(
            'resampleScratch',
            'loadImageFileToScratch',
            'saveScratchToFile',
        ));

        // Create a source 'image' on the VFS that can be copied and compared if a resize isn't necessary
        file_put_contents($sourcePath, uniqid());

        $image->setWidth($sourceW);
        $image->setHeight($sourceH);
        $image->setFilePath($sourcePath, false);

        // Image was larger than constraints, so expect to be resized
        if ($resize) {
            $image  ->expects($this->once())
                ->method('resampleScratch')
                ->with($this->equalTo($resizedW), $this->equalTo($resizedH));
        }

        ISC_PRODUCT_IMAGE::createResizedFile($image, $constrainW, $constrainH, $destPath, $writeOptions);

        // Image was within constraints, so expect image to be copied
        if (!$resize) {
            $this->assertFileExists($destPath);
            $this->assertFileEquals($sourcePath, $destPath);
        }
    }

    public function testProductImageCounterURL()
    {
        $counter     = 42;
        $imageId     = 1234;
        $productHash = 'foobar';
        $file = uniqid();
        $sourcePath = $this->_vfsRoot . '/' . $file . '.jpg';
        file_put_contents($sourcePath, 'a');

        $image = $this->getMock('\ISC_PRODUCT_IMAGE', array('getAbsoluteSourceFilePath', 'getSourceModifiedTime'));
        $image
            ->expects($this->any())
            ->method('getAbsoluteSourceFilePath')
            ->will($this->returnValue($sourcePath));

        $image
            ->expects($this->any())
            ->method('getSourceModifiedTime')
            ->will($this->returnValue('1234567'));

        $image->setProductImageId($imageId);
        $image->setProductHash($productHash);

        // HACK: Appease Store/Cdn/Environment.php:90 - calling getDynamicImageResizerUrl() triggers
        //       instantiation of Store_Cdn, which then goes looking for environmental config.
        Store_Config::override('ShopPath', 'https://quux.com');
        try {
            $url = $image->getDynamicImageResizerUrl(100, 100, $bg = null, $ssl = true);
            $this->assertEquals("/products/${productHash}/images/${imageId}/${file}.1234567.100.100.jpg?c=${counter}", $url);
        } catch (Exception $e) {
            Store_Config::revert('ShopPath');
            throw $e;
        }
    }

    public function testIsSample()
    {
        $image = new ISC_PRODUCT_IMAGE();
        $image->setSourceFilePath("%%SAMPLE%%/123.jpg");

        $this->assertTrue($image->isSample());
    }

    public function testIsNotSample()
    {
        $image = new ISC_PRODUCT_IMAGE();
        $image->setSourceFilePath("something/123.jpg");

        $this->assertFalse($image->isSample());
    }

    public function testGetAbsoluteSourceFilePathSample()
    {
        $image = new ISC_PRODUCT_IMAGE();
        $image->setSourceFilePath("%%SAMPLE%%/123.jpg");
        $this->assertEquals('/fake/app/assets/img/sample_images/123.jpg', $image->getAbsoluteSourceFilePath('/fake'));
    }

    public function testGetAbsoluteSourceFilePathNonSample()
    {
        $image = new ISC_PRODUCT_IMAGE();
        $image->setSourceFilePath("non_sample_images/123.jpg");
        $this->assertEquals('asset://file/non_sample_images/123.jpg', $image->getAbsoluteSourceFilePath('/fake'));
    }

    public function testGetAbsoluteSourceFilePathWithEmptySourcePathReturnsEmptyString()
    {
        $image = new ISC_PRODUCT_IMAGE();
        $this->assertEquals('', $image->getAbsoluteSourceFilePath());
    }
}

class MockImage extends ISC_IMAGE_LIBRARY_GD implements ISC_IMAGE_LIBRARY_INTERFACE {
    public function setHeight($height) {
        $this->_height = $height;
    }
    public function setWidth($width) {
        $this->_width = $width;
    }
}
