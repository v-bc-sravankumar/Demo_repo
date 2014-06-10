<?php

namespace Unit\Image\ImageMagick;

use org\bovigo\vfs\vfsStream;
use PHPUnit_Framework_TestCase;
use ISC_IMAGE_LIBRARY_IMAGICK;
use ISC_IMAGE_WRITEOPTIONS_PNG;
use Imagick;
use Store_Feature;

class ImageMagickTest extends PHPUnit_Framework_TestCase
{
    protected $imagickEnabled;

    public function setUp()
    {
        $this->imagickEnabled = Store_Feature::isEnabled('ImageLibraryImageMagick');

        vfsStream::setup();
        vfsStream::create(array(
            'images' => array(
                'invalid.jpg' => '',
            ),
        ));
    }

    public function tearDown()
    {
        Store_Feature::override('ImageLibraryImageMagick', $this->imagickEnabled);
    }

    public function testIsLibrarySupportedWithFlagEnabledIsTrue()
    {
        Store_Feature::override('ImageLibraryImageMagick', true);
        $this->assertTrue(ISC_IMAGE_LIBRARY_IMAGICK::isLibrarySupported());
    }

    public function testIsLibrarySupportedWithFlagDisabledIsFalse()
    {
        Store_Feature::override('ImageLibraryImageMagick', false);
        $this->assertFalse(ISC_IMAGE_LIBRARY_IMAGICK::isLibrarySupported());
    }

    public function testIsFileSupportedIsFalseForInvalidFilePath()
    {
        $this->assertFalse(ISC_IMAGE_LIBRARY_IMAGICK::isFileSupported('foo'));
    }

    public function testIsFileSupportedIsFalseForUnsupportedImage()
    {
        $this->assertFalse(ISC_IMAGE_LIBRARY_IMAGICK::isFileSupported(__DIR__ . '/icon.ico'));
    }

    public function testGetSupportedImageTypes()
    {
        // we should at minimum support gif, jpeg and png
        $expectedTypes = array(
            IMAGETYPE_GIF,
            IMAGETYPE_JPEG,
            IMAGETYPE_PNG,
        );

        $this->assertEquals($expectedTypes, array_intersect($expectedTypes, ISC_IMAGE_LIBRARY_IMAGICK::getSupportedImageTypes()));
    }

    /**
     * @expectedException ISC_IMAGE_BASECLASS_FILENOTFOUND_EXCEPTION
     */
    public function testLoadImageFileToScratchForInvalidPathThrowsException()
    {
        $image = new ISC_IMAGE_LIBRARY_IMAGICK('foo', false); // pass false here to bypass base class image info loading
        $image->loadImageFileToScratch();
    }

    /**
     * @expectedException ISC_IMAGE_BASECLASS_INVALIDIMAGE_EXCEPTION
     */
    public function testLoadImageFileToScratchForInvalidImageThrowsException()
    {
        $image = new ISC_IMAGE_LIBRARY_IMAGICK(vfsStream::url('images/invalid.jpg'), false); // pass false here to bypass base class image info loading
        $image->loadImageFileToScratch();
    }

    public function testLoadImageFileToScratchForValidImage()
    {
        $image = new ISC_IMAGE_LIBRARY_IMAGICK(__DIR__ . '/logo.png');
        $image->loadImageFileToScratch();

        $this->assertInstanceOf('Imagick', $image->getScratchResource());
    }

    public function testLoadImageFileToScratchForStreamPath()
    {
        $filePath = vfsStream::url('images/stream.png');
        copy(__DIR__ . '/logo.png', $filePath);

        $image = new ISC_IMAGE_LIBRARY_IMAGICK($filePath);
        $image->loadImageFileToScratch();

        $this->assertInstanceOf('Imagick', $image->getScratchResource());
    }

    private function assertValidImage($filePath, $imageType = IMAGETYPE_PNG)
    {
        $this->assertFileExists($filePath);

        $info = @getimagesize($filePath);
        $this->assertNotEmpty($info);

        $this->assertEquals($imageType, $info[2]);

        return $info;
    }

    public function testSaveScratchToFile()
    {
        $filePath = vfsStream::url('images/savetofile.png');

        $image = new ISC_IMAGE_LIBRARY_IMAGICK(__DIR__ . '/logo.png');
        $image->loadImageFileToScratch();
        $image->saveScratchToFile($filePath, new ISC_IMAGE_WRITEOPTIONS_PNG());

        $this->assertValidImage($filePath);
    }

    public function testSaveScratchToStream()
    {
        $filePath = vfsStream::url('images/savetostream.png');
        $handle = fopen($filePath, 'wb');

        $image = new ISC_IMAGE_LIBRARY_IMAGICK(__DIR__ . '/logo.png');
        $image->loadImageFileToScratch();
        $image->saveScratchToStream($handle, new ISC_IMAGE_WRITEOPTIONS_PNG());

        fclose($handle);

        $this->assertValidImage($filePath);
    }

    public function testSaveScratchToOutput()
    {
        $filePath = vfsStream::url('images/savetooutput.png');

        ob_start();

        $image = new ISC_IMAGE_LIBRARY_IMAGICK(__DIR__ . '/logo.png');
        $image->loadImageFileToScratch();
        $image->saveScratchToOutput(new ISC_IMAGE_WRITEOPTIONS_PNG());

        file_put_contents($filePath, ob_get_contents());
        ob_end_clean();

        $this->assertValidImage($filePath);
    }

    /**
     * Loads an image from a stream URL by copying to a temp file.
     * This works around a regression in ImageMagick in 3.1.0RC2.
     *
     * @return Imagick
     */
    private function loadImagickFromStream($filePath)
    {
        $handle = fopen($filePath, 'rb');
        $image = new Imagick();
        $image->readImageFile($handle);

        fclose($handle);

        return $image;
    }

    public function testResizeScratch()
    {
        $filePath = vfsStream::url('images/resized.png');

        $image = new ISC_IMAGE_LIBRARY_IMAGICK(__DIR__ . '/logo.png');
        $image->loadImageFileToScratch();
        $image->resizeScratch(508, 120, '000000');
        $image->saveScratchToFile($filePath, new ISC_IMAGE_WRITEOPTIONS_PNG());

        list($width, $height) = $this->assertValidImage($filePath);

        $this->assertEquals(508, $width);
        $this->assertEquals(120, $height);

        $resizedImage = $this->loadImagickFromStream($filePath);
        $compareToImage = new Imagick(__DIR__ . '/logo-resized.png');

        list(, $mae) = $compareToImage->compareImages($resizedImage, Imagick::METRIC_MEANABSOLUTEERROR);

        // check error to be less than 0.1%
        $this->assertLessThan(0.001, $mae);
    }

    public function testResampleScratch()
    {
        $filePath = vfsStream::url('images/resized.png');

        $image = new ISC_IMAGE_LIBRARY_IMAGICK(__DIR__ . '/logo.png');
        $image->loadImageFileToScratch();
        $image->resampleScratch(100, 50);
        $image->saveScratchToFile($filePath, new ISC_IMAGE_WRITEOPTIONS_PNG());

        list($width, $height) = $this->assertValidImage($filePath);

        $this->assertEquals(100, $width);
        $this->assertEquals(50, $height);

        $resizedImage = $this->loadImagickFromStream($filePath);
        $compareToImage = new Imagick(__DIR__ . '/logo-resampled.png');

        list(, $mae) = $compareToImage->compareImages($resizedImage, Imagick::METRIC_MEANABSOLUTEERROR);

        // check error to be less than 0.1%
        $this->assertLessThan(0.001, $mae);
    }
}
