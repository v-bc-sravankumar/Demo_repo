<?php

class Unit_Lib_Store_CustomUrl_Validation extends Interspire_IntegrationTest
{

	public function testInvalidCustomPatternProducts()
	{
		$invalidPatterns = array(
			'',
			'/',

			// directory references
			'/./test/%productname%',
			'/../test/%productname%',
			'/test/../%productname%',
			'/%productname%/..',
			'/../../../%productname%',

			// invalid characters
			'/*test/@cat/%productname%',

			// placeholder check
			'/test/product',
			'/test/%categoryname%',

			// restricted files
			'/admin/%productname%',
			'/index.php/%productname%',
		);

		$this->assertIsCustomPatternValid(Store_CustomUrl::TARGET_TYPE_PRODUCT, $invalidPatterns, false);
	}

	public function testValidCustomPatternProducts()
	{
		$validPatterns = array(
			'/%productname%',
			'/products/%productname%.html',
			'/my-custom-format/%productid%/12345',
			'/%category%/%sku%/%upc%/',
		);

		$this->assertIsCustomPatternValid(Store_CustomUrl::TARGET_TYPE_PRODUCT, $validPatterns, true);
	}

	public function testValidCustomPatternCategories()
	{
		$validPatterns = array(
			'/%categoryname%',
			'/products/%categoryname%.html',
			'/my-custom-format/%categoryid%/12345',
			'/%parent%/%categoryid%/%categoryname%/',
		);

		$this->assertIsCustomPatternValid(Store_CustomUrl::TARGET_TYPE_CATEGORY, $validPatterns, true);
	}

	public function testInvalidPatternCustomCategories()
	{
		$invalidPatterns = array(
			'',
			'/',

			// directory references
			'/./test/%categoryname%',
			'/../test/%categoryname%',
			'/test/../%categoryname%',
			'/%categoryname%/..',
			'/../../../%categoryname%',

			// invalid characters
			'/*test/@cat/%categoryname%',

			// placeholder check
			'/test/category',
			'/test/%productname%',

			// restricted files
			'/admin/%categoryname%',
			'/index.php/%categoryname%',
		);

		$this->assertIsCustomPatternValid(Store_CustomUrl::TARGET_TYPE_CATEGORY, $invalidPatterns, false);
	}

	public function testValidCustomPatternWebPages()
	{
		$validPatterns = array(
			'/%pagename%',
			'/pages/%pagename%.html',
			'/my-custom-format/%pageid%/12345',
			'/%parent%/%pageid%/%pagename%/',
		);

		$this->assertIsCustomPatternValid(Store_CustomUrl::TARGET_TYPE_PAGE, $validPatterns, true);
	}

	public function testInvalidCustomPatternWebPages()
	{
		$invalidPatterns = array(
			'',
			'/',

			// directory references
			'/./test/%pagename%',
			'/../test/%pagename%',
			'/test/../%pagename%',
			'/%pagename%/..',
			'/../../../%pagename%',

			// invalid characters
			'/*test/@cat/%pagename%',

			// placeholder check
			'/test/page',
			'/test/%productname%',

			// restricted files
			'/admin/%pagename%',
			'/index.php/%pagename%',
		);

		$this->assertIsCustomPatternValid(Store_CustomUrl::TARGET_TYPE_PAGE, $invalidPatterns, false);
	}

	public function testValidCustomPatternNewsItem()
	{
		$validPatterns = array(
			'/%postname%',
			'/news/%postname%.html',
			'/my-custom-format/%postid%/12345',
			'/%postid%/%postname%/',
		);

		$this->assertIsCustomPatternValid(Store_CustomUrl::TARGET_TYPE_NEWS, $validPatterns, true);
	}

	public function testInvalidCustomPatternNewsItem()
	{
		$invalidPatterns = array(
			'',
			'/',

			// directory references
			'/./test/%postname%',
			'/../test/%postname%',
			'/test/../%postname%',
			'/%postname%/..',
			'/../../../%postname%',

			// invalid characters
			'/*test/@cat/%postname%',

			// placeholder check
			'/test/news',
			'/test/%productname%',

			// restricted files
			'/admin/%postname%',
			'/index.php/%postname%',
		);

		$this->assertIsCustomPatternValid(Store_CustomUrl::TARGET_TYPE_NEWS, $invalidPatterns, false);
	}

	public function assertIsCustomPatternValid($target, $patterns, $assertTrue)
	{
		foreach ($patterns as $pattern) {
			$result = Store_CustomUrl::isCustomPatternValid($target, $pattern);
			if ($assertTrue) {
				$this->assertTrue($result, "Pattern '" . $pattern . "' for " . $target . " should be valid");
			}
			else {
				$this->assertFalse($result, "Pattern '" . $pattern . "' for " . $target . " should be invalid");
			}
		}
	}

}