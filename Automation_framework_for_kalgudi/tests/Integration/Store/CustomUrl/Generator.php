<?php

class Unit_Lib_Store_CustomUrl_Generator extends Interspire_IntegrationTest
{

	public function testGenerateProductUrlForCustomFormat()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'custom',
			'pattern' => '/products/%category%/%productname%.html',
			'unique' => false,
			'replacements' => array(
				'category' => 'Category Name!',
				'productname' => ' Product / Name',
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/products/category-name/product-name.html', $url);
	}

	public function testGenerateProductUrlWithAllPlaceholders()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'custom',
			'pattern' => '/product/%category%/%sku%/%upc%/%productname%.%productid%.html',
			'unique' => false,
			'replacements' => array(
				'category' => 'Category Name!',
				'productname' => ' Product / Name',
				'sku' => 'SKU-1',
				'upc' => 'UPC-1',
				'productid' => '123',
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/product/category-name/sku-1/upc-1/product-name.123.html', $url);
	}

	public function testGenerateCategoryUrlWithAllPlaceholders()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_CATEGORY,
			'format' => 'custom',
			'pattern' => '/categories/%parent%/%categoryname%/%categoryid%/',
			'unique' => false,
			'replacements' => array(
				'parent' => 'Root Cat!',
				'categoryname' => ' Sub / Cat',
				'categoryid' => '123'
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/categories/root-cat/sub-cat/123/', $url);
	}

	public function testGeneratePageUrlWithAllPlaceholders()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PAGE,
			'format' => 'custom',
			'pattern' => '/page/%parent%/%pagename%/%pageid%.html',
			'unique' => false,
			'replacements' => array(
				'parent' => 'Parent Page!',
				'pagename' => ' My Page Title',
				'pageid' => '123'
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/page/parent-page/my-page-title/123.html', $url);
	}

	public function testGenerateNewsUrlWithAllPlaceholders()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_NEWS,
			'format' => 'custom',
			'pattern' => '/news/%postname%/%postid%.html',
			'unique' => false,
			'replacements' => array(
				'postname' => ' My News Article!',
				'postid' => '123'
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/news/my-news-article/123.html', $url);
	}

	public function testGenerateProductUrlForSeoShort()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'seo_short',
			'unique' => false,
			'replacements' => array(
				'category' => 'Category Name!',
				'productname' => ' Product / Name',
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/product-name/', $url);
	}

	public function testGenerateProductUrlForSeoLong()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'seo_long',
			'unique' => false,
			'replacements' => array(
				'category' => 'Category Name!',
				'productname' => ' Product / Name',
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/products/product-name.html', $url);
	}

	public function testGenerateProductUrlForSeoCategory()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'seo_category',
			'unique' => false,
			'replacements' => array(
				'category' => 'Category Name!',
				'productname' => ' Product / Name',
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/category-name/product-name/', $url);
	}

	public function testGenerateProductUrlFromEmptyCategory()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'custom',
			'pattern' => '/products/%category%/%productname%.html',
			'unique' => false,
			'replacements' => array(
				'category' => '',
				'productname' => 'My Product',
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/products/my-product.html', $url);
	}

	public function testGenerateProductUrlFromDiacritics()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'custom',
			'pattern' => '/products/%category%/%productname%/%sku%.html',
			'unique' => false,
			'replacements' => array(
				'category' => 'Coração',
				'productname' => 'Grüßen',
				'sku' => 'Væske',
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/products/coracao/grussen/vaeske.html', $url);
	}

	public function testGenerateProductUrlWithUnderscores()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'custom',
			'pattern' => '/product/%productname%.html',
			'unique' => false,
			'replacements' => array(
				'productname' => 'Product_Name_With_Underscores',
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/product/product_name_with_underscores.html', $url);
	}

	public function testGenerateProductUrlFromQuotesAndEmDash()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'custom',
			'pattern' => '/product/%productname%.html',
			'unique' => false,
			'replacements' => array(
				'productname' => 'It’s “All Good” — Isn’t It?',
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/product/its-all-good-isnt-it.html', $url);
	}

	public function testGenerateProductUrlFromMultipleDashes()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'custom',
			'pattern' => '/product/%productname%.html',
			'unique' => false,
			'replacements' => array(
				'productname' => 'Is It --- All Good?',
			),
		);

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals('/product/is-it-all-good.html', $url);
	}

	public function testGenerateProductUrlWithLongName()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'custom',
			'pattern' => '/product/%productname%/test.html',
			'unique' => false,
			'replacements' => array(
				'productname' => '123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-ABCDEFGHIJKLMNOPQRSTUVWXYZ',
			),
		);

		$expectedUrl = '/product/123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-123456789-abcde/test.html';

		$url = Store_CustomUrl::generateUrl($options);
		$this->assertEquals($expectedUrl, $url);
	}

	// TODO: @expectedExceptions not handled because PHPUnit 3.4
	// does not wrap and throw E_USER_NOTICE exceptions so these
	// errors are currently untestable, and will show up in the
	// logs on Bamboo as side effects.

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCannotGenerateUrlWithEmptyOptions()
	{
		$options = array();
		$this->assertFalse(Store_CustomUrl::generateUrl($options));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCannotGenerateUrlWithInvalidType()
	{
		$options = array(
			'type' => 'derp',
			'format' => 'custom',
			'replacements' => array(),
			'pattern' => 'bar',
			'unique' => false,
		);

		$this->assertFalse(Store_CustomUrl::generateUrl($options));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCannotGenerateUrlWithInvalidFormat()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'foo',
			'replacements' => array(
				'foo' => 'bar',
			),
			'pattern' => 'bar',
			'unique' => false,
		);

		$this->assertFalse(Store_CustomUrl::generateUrl($options));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCannotGenerateUrlWithUrlConstType()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_URL, // ???
			'format' => 'custom',
			'replacements' => array(),
			'pattern' => 'bar',
			'unique' => false,
		);

		$this->assertFalse(Store_CustomUrl::generateUrl($options));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCannotGenerateUrlFromEmptyReplacements()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'custom',
			'replacements' => array(),
			'pattern' => 'bar',
			'unique' => false,
		);

		$this->assertFalse(Store_CustomUrl::generateUrl($options));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCannotGenerateUrlWithNoPlaceholder()
	{
		$options = array(
			'type' => Store_CustomUrl::TARGET_TYPE_PRODUCT,
			'format' => 'custom',
			'replacements' => array(
				'foo' => 'bar',
			),
			'pattern' => 'bar',
			'unique' => false,
		);

		$this->assertFalse(Store_CustomUrl::generateUrl($options));
	}

}
