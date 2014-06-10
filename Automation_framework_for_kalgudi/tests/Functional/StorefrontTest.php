<?php

/**
 * Basic functional test, verifying that the sample data is installed correctly and the storefront
 * can be accessed via the web.
 */
class StorefrontTest extends Interspire_FunctionalTest
{

	public function makeUrl($path)
	{
		return TEST_APPLICATION_URL . $path;
	}

	public function testHomepagePresence()
	{
		$this->get($this->makeUrl('/'));
		$this->assertStatus(200);
		$this->assertContentType("text/html");
	}

	public function testRssPresence()
	{
		$this->get($this->makeUrl('/rss.php?type=rss&action=featuredproducts'));
		$this->assertStatus(200);
		$this->assertContentType('text/xml');
	}

	public function testSitemapPresence()
	{
		$this->get($this->makeUrl('/sitemap/'));
		$this->assertStatus(200);
		$this->assertContentType('text/html');
	}

	public function testCartPresence()
	{
		$this->get($this->makeUrl('/cart.php'));
		$this->assertStatus(200);
		$this->assertContentType('text/html');
		$this->assertText('Your Shopping Cart');
	}

	public function testRootCategoryPresence()
	{
		$this->get($this->makeUrl('/shop-iphone/'));
		$this->assertStatus(200);
		$this->assertContentType('text/html');
		$this->assertText('Shop iPhone');
	}

	public function testSubCategoryPresence()
	{
		$this->get($this->makeUrl('/accessories/'));
		$this->assertStatus(200);
		$this->assertContentType('text/html');
		$this->assertText('Accessories');
	}

	public function testProductPresence()
	{
		$this->get($this->makeUrl('/sample-product-mac-pro/'));
		$this->assertStatus(200);
		$this->assertContentType('text/html');
		$this->assertText('[Sample Product] Mac Pro');
	}

	public function testBrandPresence()
	{
		$this->get($this->makeUrl('/brands/Apple.html'));
		$this->assertStatus(200);
		$this->assertContentType('text/html');
		$this->assertText('Apple');
	}

	public function testPagePresence()
	{
		$this->get($this->makeUrl('/shipping-returns/'));
		$this->assertStatus(200);
		$this->assertContentType('text/html');
		$this->assertText('Shipping & Returns');
	}

}