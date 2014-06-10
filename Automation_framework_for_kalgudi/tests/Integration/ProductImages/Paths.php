<?php

class Unit_ProductImages_Paths extends Interspire_IntegrationTest
{
	public function setUp ()
	{
		parent::setUp();
		require_once BUILD_ROOT . '/admin/init.php';
	}

	public function testSafeModePathGeneration ()
	{
		$result = ISC_PRODUCT_IMAGE::generateSourceImageRelativeFilePath('filename.ext', true);
		$this->assertRegExp('#^[a-z]/filename__[0-9]{5}\\.ext$#', $result, 'Safe-mode path generation failed.');
	}

	public function testNonSafeModePathGeneration ()
	{
		$result = ISC_PRODUCT_IMAGE::generateSourceImageRelativeFilePath('filename.ext', false);
		$this->assertRegExp('#^[a-z]/[0-9]{3}/filename__[0-9]{5}\\.ext$#', $result, 'Non-safe-mode path generation failed.');
	}

	public function testPrependToFileExtension ()
	{
		$this->assertEquals('filename__prepend__.ext', ISC_PRODUCT_IMAGE::prependToFileExtension('filename.ext', '__prepend__'), 'Prepending to extension failed for filename with extension.');
		$this->assertEquals('filename', ISC_PRODUCT_IMAGE::prependToFileExtension('filename', '__prepend__'), 'Prepending to extension failed for filename without extension.');
		$this->assertEquals('__prepend__.filename', ISC_PRODUCT_IMAGE::prependToFileExtension('.filename', '__prepend__'), 'Prepending to extension failed for extension-like failename.');
	}
}
