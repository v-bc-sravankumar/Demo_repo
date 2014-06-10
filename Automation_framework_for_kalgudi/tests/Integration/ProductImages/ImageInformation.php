<?php

class Unit_ProductImages_ImageInformation extends Interspire_IntegrationTest
{
	public function setUp ()
	{
		parent::setUp();
		require_once BUILD_ROOT . '/admin/init.php';
		Interspire_StreamRouter::removeRoute('getimagefromurl');
	}

	public function tearDown()
	{
		Interspire_StreamRouter::removeRoute('getimagefromurl');
		parent::tearDown();
	}

	public function testWithoutFilePath ()
	{
		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance();
		$this->assertInstanceOf('ISC_IMAGE_LIBRARY_INTERFACE', $library);
	}

	public function testImageWidth ()
	{
		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance(dirname(__FILE__) . '/1x1.gif');
		$width = $library->getWidth();
		$this->assertEquals(1, $width, 'Expected width of 1 pixel but found ' . $width . ' instead.');
	}

	public function testImageHeight ()
	{
		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance(dirname(__FILE__) . '/1x1.gif');
		$height = $library->getHeight();
		$this->assertEquals(1, $height, 'Expected height of 1 pixel but found ' . $height . ' instead.');
	}

	public function testGifBits ()
	{
		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance(dirname(__FILE__) . '/1x1.gif');
		$bits = $library->getBits();
		$this->assertEquals(8, $bits, 'Expected 8 bits but found ' . $bits . ' instead.'); // gifs are 8 bits x 3 channels per colour of it's numbered palette, the index just happens to also be 8 bits (256 colours)
	}

	public function testGifChannels ()
	{
		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance(dirname(__FILE__) . '/1x1.gif');
		$channels = $library->getChannels();
		$this->assertEquals(3, $channels, 'Expected 3 channels but found ' . $channels . ' instead.');
	}

	public function testGifImageType ()
	{
		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance(dirname(__FILE__) . '/1x1.gif');
		$type = $library->getImageType();
		$this->assertEquals(IMAGETYPE_GIF, $type, 'Expected IMAGETYPE_GIF (' . IMAGETYPE_GIF . ') but found ' . $type . ' instead.');
	}

	public function testGifExtension ()
	{
		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance(dirname(__FILE__) . '/1x1.gif');
		$ext = $library->getImageTypeExtension();
		$this->assertEquals('.gif', $ext, 'Expected ".gif" from gif file but found "' . $ext . '" instead.');

		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance(dirname(__FILE__) . '/1x1gif.jpg');
		$ext = $library->getImageTypeExtension();
		$this->assertEquals('.gif', $ext, 'Expected ".gif" from gif named as .jpg but found "' . $ext . '" instead.');
	}

	public function testJpegBits ()
	{
		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance(dirname(__FILE__) . '/Nasa_blue_marble.jpg.blah');
		$bits = $library->getBits();
		$this->assertEquals(8, $bits, 'Expected 8 bits but found ' . $bits . ' instead.'); // jpegs are 8 bits x 3 channels = 24 bits total
	}

	public function testJpegChannels ()
	{
		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance(dirname(__FILE__) . '/Nasa_blue_marble.jpg.blah');
		$channels = $library->getChannels();
		$this->assertEquals(3, $channels, 'Expected 3 channels but found ' . $channels . ' instead.');
	}

	public function testJpegImageType ()
	{
		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance(dirname(__FILE__) . '/Nasa_blue_marble.jpg.blah');
		$type = $library->getImageType();
		$this->assertEquals(IMAGETYPE_JPEG, $type, 'Expected IMAGETYPE_JPEG (' . IMAGETYPE_JPEG . ') but found ' . $type . ' instead.');
	}

	public function testJpegExtension ()
	{
		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance(dirname(__FILE__) . '/Nasa_blue_marble.jpg.blah');
		$ext = $library->getImageTypeExtension();
		$this->assertEquals('.jpg', $ext, 'Expected ".jpg" from jpg file but found "' . $ext . '" instead.');
	}

	public function testCanGetImageFromUrl ()
	{
		Interspire_StreamRouter::addRoute('getimagefromurl', '#^getimagefromurl://test(/(.*)|)$#', __DIR__ . '$1');
		$library = ISC_IMAGE_LIBRARY_FACTORY::getImageLibraryInstance('getimagefromurl://test/Nasa_blue_marble.jpg.blah');
		$this->assertInstanceOf('ISC_IMAGE_LIBRARY_INTERFACE', $library);
	}
}
