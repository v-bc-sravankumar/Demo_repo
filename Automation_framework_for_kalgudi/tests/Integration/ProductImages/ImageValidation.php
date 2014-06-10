<?php

class Unit_ProductImages_ImageValidation extends Interspire_IntegrationTest
{
	public function setUp ()
	{
		parent::setUp();
		require_once BUILD_ROOT . '/admin/init.php';
	}

	public function testInvalidImage ()
	{
		$this->assertEquals(false, ISC_PRODUCT_IMAGE::isValidImageFile(dirname(__FILE__) . '/invalid.jpg'), 'An invalid image was incorrectly found to be valid.');
	}

	public function testValidImage ()
	{
		$this->assertEquals(true, ISC_PRODUCT_IMAGE::isValidImageFile(dirname(__FILE__) . '/1x1.gif'), 'A valid image was incorrectly found to be invalid.');
	}

	public function testValidImageWrongExtension ()
	{
		$this->assertEquals(true, ISC_PRODUCT_IMAGE::isValidImageFile(dirname(__FILE__) . '/1x1gif.jpg'), 'A valid image with incorrect extension was incorrectly found to be invalid.');
	}
}
