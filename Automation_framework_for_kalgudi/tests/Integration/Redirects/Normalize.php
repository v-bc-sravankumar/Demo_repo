<?php

GetLib('class.redirects');

class Unit_Core_Redirects extends Interspire_IntegrationTest
{
	const ROOT_SHOPPATH = 'http://www.example.com:81';
	const ROOT_SHOPPATH_NO_WWW = 'http://example.com:81';
	const ROOT_SHOPPATHSSL = 'https://www.example.com:81';
	const ROOT_APPPATH = '';

	const SUBDIR_DOMAIN = 'http://www.example.com:81';
	const SUBDIR_SHOPPATH = 'http://www.example.com:81/foo';
	const SUBDIR_SHOPPATH_NO_WWW = 'http://example.com:81/foo';
	const SUBDIR_SHOPPATHSSL = 'https://www.example.com:81/foo';
	const SUBDIR_APPPATH = '/foo';

	private $originalConfig;

	public function setUp ()
	{
		parent::setUp();
		GetLib('class.redirects');

		$this->originalConfig = array(
			'ShopPath' => Store_Config::get('ShopPath'),
			'ShopPathSSL' => Store_Config::get('ShopPathSSL'),
			'AppPath' => Store_Config::get('AppPath'),
			'RedirectWWW' => Store_Config::get('RedirectWWW'),
		);
	}

	public function tearDown()
	{
		foreach ($this->originalConfig as $setting => $value) {
			Store_Config::override($setting, $value);
		}
	}

	public function dataSavingNewUrlForRootInstalls ()
	{
		return array(
			array('', false),
			array('www.interspire.com', false),
			array('/', '/'),
			array('/bar', '/bar'),
			array('/bar/', '/bar/'),
			array('/bar?alpha', '/bar?alpha'),
			array('/bar?alpha=beta', '/bar?alpha=beta'),
			array(self::ROOT_SHOPPATH, ''),
			array(self::ROOT_SHOPPATH . '/', '/'),
			array(self::ROOT_SHOPPATH . '/bar', '/bar'),
			array(self::ROOT_SHOPPATH . '/bar/', '/bar/'),
			array('http://www.interspire.com', null),
			array('http://www.interspire.com/', null),
			array('http://www.interspire.com/foo', null),
			array('http://www.interspire.com/foo/', null),
			array('https://www.interspire.com', null),
			array('https://www.interspire.com/', null),
			array('https://www.interspire.com/foo', null),
			array('https://www.interspire.com/foo/', null),
			array('ftp://www.interspire.com', false),
			array('www.interspire.com', false),
		);
	}

	/** @dataProvider dataSavingNewUrlForRootInstalls */
	public function testSavingNewUrlForRootInstalls ($input, $expected)
	{
		if ($expected === null) {
			$expected = $input;
		}

		Store_Config::override('ShopPath', self::ROOT_SHOPPATH);
		Store_Config::override('ShopPathSSL', self::ROOT_SHOPPATHSSL);
		Store_Config::override('AppPath', self::ROOT_APPPATH);

		$output = ISC_REDIRECTS::normalizeNewURLForDatabase($input);

		$this->assertEquals($expected, $output, "for a store at " . self::ROOT_SHOPPATH . " a redirect to '$input' should be saved as '$expected' but '$output' was returned instead'");
	}

	public function dataSavingNewUrlForSubdirInstalls ()
	{
		return array(
			array('', false),
			array('www.interspire.com', false),
			array('/', '/'),
			array('/bar', '/bar'),
			array('/bar/', '/bar/'),
			array('/bar?alpha', '/bar?alpha'),
			array('/bar?alpha=beta', '/bar?alpha=beta'),
			array(self::SUBDIR_SHOPPATH, ''),
			array(self::SUBDIR_SHOPPATH . '/', '/'),
			array(self::SUBDIR_SHOPPATH . '/bar', '/bar'),
			array(self::SUBDIR_SHOPPATH . '/bar/', '/bar/'),
			array(self::SUBDIR_DOMAIN, null),
			array(self::SUBDIR_DOMAIN . '/', null),
			array(self::SUBDIR_DOMAIN . '/alpha', null),
			array('http://www.interspire.com', null),
			array('http://www.interspire.com/', null),
			array('http://www.interspire.com/foo', null),
			array('http://www.interspire.com/foo/', null),
			array('https://www.interspire.com', null),
			array('https://www.interspire.com/', null),
			array('https://www.interspire.com/foo', null),
			array('https://www.interspire.com/foo/', null),
			array('ftp://www.interspire.com', false),
			array('www.interspire.com', false),
		);
	}

	/** @dataProvider dataSavingNewUrlForSubdirInstalls */
	public function testSavingNewUrlForSubdirInstalls ($input, $expected)
	{
		if ($expected === null) {
			$expected = $input;
		}

		Store_Config::override('ShopPath', self::SUBDIR_SHOPPATH);
		Store_Config::override('ShopPathSSL', self::SUBDIR_SHOPPATHSSL);
		Store_Config::override('AppPath', self::SUBDIR_APPPATH);

		$output = ISC_REDIRECTS::normalizeNewURLForDatabase($input);

		$this->assertEquals($expected, $output, "for a store at " . self::SUBDIR_SHOPPATH . " a redirect to '$input' should be saved as '$expected' but '$output' was returned instead'");
	}

	public function dataManualRedirectsForRootInstalls ()
	{
		return array(
			array('/foo', '/', '/foo', '/'),
			array('/foo', self::ROOT_SHOPPATH . '', '/foo', '/'),
			array('/foo', self::ROOT_SHOPPATH . '/', '/foo', '/'),
			array('/foo', '/bar', '/foo', '/bar'),
			array('/foo?alpha=beta', '/bar', '/foo?alpha=beta', '/bar'),
		);
	}

	/** @dataProvider dataManualRedirectsForRootInstalls */
	public function testManualRedirectsForRootInstalls ($redirectFrom, $redirectTo, $inputUrl, $expected)
	{
		Store_Config::override('ShopPath', self::ROOT_SHOPPATH);
		Store_Config::override('ShopPathSSL', self::ROOT_SHOPPATHSSL);
		Store_Config::override('AppPath', self::ROOT_APPPATH);

		$insert = array(
			'redirectpath' => $redirectFrom,
			'redirectassocid'=> 0,
			'redirectassoctype' => ISC_REDIRECTS::REDIRECT_TYPE_MANUAL,
			'redirectmanual' => ISC_REDIRECTS::normalizeNewURLForDatabase($redirectTo),
		);

		$redirectId = $this->fixtures->InsertQuery('redirects', $insert);

		$output = ISC_REDIRECTS::generateRedirectUrl($inputUrl);

		$this->fixtures->DeleteQuery('redirects', "WHERE redirectid = " . (int)$redirectId);

		$this->assertEquals($expected, $output, "for a store at " . self::ROOT_SHOPPATH . " with a redirect from '$redirectFrom' to '$redirectTo', a request for '$inputUrl' should send back a Location header for '$expected' but '$output' was returned instead");
	}

	public function dataManualRedirectsForSubdirInstalls ()
	{
		return array(
			array(
				'/bar',
				'/baz',
				self::SUBDIR_APPPATH . '/bar',
				self::SUBDIR_APPPATH . '/baz',
			),
			array(
				'/bar',
				'/baz',
				'/bar',
				false,
			),
			array(
				'/bar',
				self::SUBDIR_DOMAIN,
				self::SUBDIR_APPPATH . '/bar',
				self::SUBDIR_DOMAIN,
			),
			array(
				'/bar',
				self::SUBDIR_DOMAIN . '/',
				self::SUBDIR_APPPATH . '/bar',
				self::SUBDIR_DOMAIN . '/',
			),
			array(
				'/bar?alpha=beta',
				'/baz',
				self::SUBDIR_APPPATH . '/bar?alpha=beta',
				self::SUBDIR_APPPATH . '/baz',
			),
			array(
				'/bar?alpha=beta',
				self::SUBDIR_APPPATH . '/baz',
				'/bar?alpha=beta',
				false,
			),
		);
	}

	/** @dataProvider dataManualRedirectsForSubdirInstalls */
	public function testManualRedirectsForSubdirInstalls ($redirectFrom, $redirectTo, $inputUrl, $expected)
	{
		Store_Config::override('ShopPath', self::SUBDIR_SHOPPATH);
		Store_Config::override('ShopPathSSL', self::SUBDIR_SHOPPATHSSL);
		Store_Config::override('AppPath', self::SUBDIR_APPPATH);

		$insert = array(
			'redirectpath' => $redirectFrom,
			'redirectassocid'=> 0,
			'redirectassoctype' => ISC_REDIRECTS::REDIRECT_TYPE_MANUAL,
			'redirectmanual' => ISC_REDIRECTS::normalizeNewURLForDatabase($redirectTo),
		);

		$redirectId = $this->fixtures->InsertQuery('redirects', $insert);
		$this->assertNotEquals(false, $redirectId);

		$output = ISC_REDIRECTS::generateRedirectUrl($inputUrl);

		$this->assertTrue($this->fixtures->DeleteQuery('redirects', "WHERE redirectid = " . (int)$redirectId));

		$this->assertEquals($expected, $output, "for a store at " . self::SUBDIR_SHOPPATH . " with a redirect from '$redirectFrom' to '$redirectTo', a request for '$inputUrl' should send back a Location header for '$expected' but '$output' was returned instead");
	}

	public function dataNormalizeShopPath()
	{
		return array(
			array(
				self::ROOT_SHOPPATH,
				REDIRECT_NO_PREFERENCE,
				null,
			),
			array(
				self::ROOT_SHOPPATH,
				REDIRECT_TO_WWW,
				null,
			),
			array(
				self::ROOT_SHOPPATH,
				REDIRECT_TO_NO_WWW,
				self::ROOT_SHOPPATH_NO_WWW,
			),
			array(
				self::ROOT_SHOPPATH_NO_WWW,
				REDIRECT_NO_PREFERENCE,
				null,
			),
			array(
				self::ROOT_SHOPPATH_NO_WWW,
				REDIRECT_TO_WWW,
				self::ROOT_SHOPPATH,
			),
			array(
				self::ROOT_SHOPPATH_NO_WWW,
				REDIRECT_TO_NO_WWW,
				null,
			),
		);
	}

	/** @dataProvider dataNormalizeShopPath */
	public function testNormalizeShopPath($shopPath, $redirectWWW, $expected)
	{
		if ($expected === null) {
			$expected = $shopPath;
		}

		$output = ISC_REDIRECTS::normalizeShopPath($shopPath, $redirectWWW);

		switch ($redirectWWW) {
			case REDIRECT_NO_PREFERENCE:
				$redirectStr = "No Preference";
				break;
			case REDIRECT_TO_WWW:
				$redirectStr = "Redirect to WWW";
				break;
			case REDIRECT_TO_NO_WWW:
				$redirectStr = "Redirect to no WWW";
				break;
		}

		$this->assertEquals($expected, $output, "for a store with a redirect www setting of '$redirectStr' and a shop path of '$shopPath', the normalized path should be '$expected' but '$output' was returned instead");
	}

	public function dataRedirectWWW()
	{
		return array(
			array(
				self::SUBDIR_SHOPPATH,
				self::SUBDIR_APPPATH,
				REDIRECT_NO_PREFERENCE,
				false,
			),
			array(
				self::SUBDIR_SHOPPATH,
				self::SUBDIR_APPPATH,
				REDIRECT_TO_WWW,
				false,
			),
			array(
				self::SUBDIR_SHOPPATH,
				self::SUBDIR_APPPATH,
				REDIRECT_TO_NO_WWW,
				self::SUBDIR_SHOPPATH_NO_WWW,
			),
			array(
				self::SUBDIR_SHOPPATH_NO_WWW,
				self::SUBDIR_APPPATH,
				REDIRECT_NO_PREFERENCE,
				false,
			),
			array(
				self::SUBDIR_SHOPPATH_NO_WWW,
				self::SUBDIR_APPPATH,
				REDIRECT_TO_WWW,
				self::SUBDIR_SHOPPATH,
			),
			array(
				self::SUBDIR_SHOPPATH_NO_WWW,
				self::SUBDIR_APPPATH,
				REDIRECT_TO_NO_WWW,
				false,
			),
		);
	}

	/**
	* @dataProvider dataRedirectWWW
	*/
	public function testRedirectWWW($shopPath, $uri, $redirectWWW, $expected)
	{
		if ($expected === null) {
			$expected = $shopPath;
		}

		$info = parse_url($shopPath);

		$_SERVER['SERVER_NAME'] = $info['host'];
		if (isset($info['port'])) {
			$_SERVER['SERVER_PORT'] = $info['port'];
		}
		else {
			$_SERVER['SERVER_PORT'] = 80;
		}
		$parts = parse_url($shopPath);
		Store_Config::override('ShopPath', $parts['host']);
		Store_Config::override('RedirectWWW', $redirectWWW);

		$_SERVER['REQUEST_URI'] = $info['path'];

		$request = new Interspire_Request(null, null, null, $_SERVER);

		$output = ISC_REDIRECTS::checkRedirectWWW($request);

		switch ($redirectWWW) {
			case REDIRECT_NO_PREFERENCE:
				$redirectStr = "No Preference";
				break;
			case REDIRECT_TO_WWW:
				$redirectStr = "Redirect to WWW";
				break;
			case REDIRECT_TO_NO_WWW:
				$redirectStr = "Redirect to no WWW";
				break;
		}

		$this->assertEquals($expected, $output, "for a store with a redirect www setting of '$redirectStr' and a shop path of '$shopPath', the store should be redirected to '$expected' but '$output' was returned instead");
	}
}
