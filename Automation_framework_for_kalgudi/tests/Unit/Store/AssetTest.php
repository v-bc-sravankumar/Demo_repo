<?php

class Unit_Store_AssetTest extends PHPUnit_Framework_TestCase
{

	protected $_shopPath;
	protected $_shopPathNormal;
	protected $_shopPathSSL;
	protected $originalConfig;

	public function setUp()
	{
		$this->originalConfig = array(
			'ShopPath' => Store_Config::get('ShopPath'),
			'ShopPathSSL' => Store_Config::get('ShopPathSSL'),
			'AppPath' => Store_Config::get('AppPath'),
		);

		$this->_shopPath = 'http://www.foobar.com';
		$this->_shopPathNormal = 'http://www.foo.com';
		$this->_shopPathSSL = 'https://www.bar.com';

		Store_Config::override('ShopPath', $this->_shopPath);
		Store_Config::override('ShopPathSSL', $this->_shopPathSSL);
		Store_Config::override('ShopPathNormal', $this->_shopPathNormal);
	}

	public function tearDown()
	{
		foreach ($this->originalConfig as $setting => $value) {
			Store_Config::override($setting, $value);
		}
	}

	public function testCanCheckAssetPath()
	{
		$input = 'asset://foo';
		$this->assertFalse(Store_Asset::isAssetPath($input));
	}

	public function testCanCheckNonAssetPath()
	{
		$input = '/foo/bar';
		$this->assertFalse(Store_Asset::isAssetPath($input));
	}

	public function testAssetPathCheckIsCaseSensitive()
	{
		$input = 'Asset://foo';
		$this->assertFalse(Store_Asset::isAssetPath($input));
	}

	public function testGeneratePathWithLeadingSlash()
	{
		$input = '/foo/bar';
		$expected = Store_Asset::getBasePath() . '/foo/bar';
		$this->assertSame($expected, Store_Asset::generatePath($input));
	}

	/**
	 * @group flaky
	 */
	public function testGenerateUrlWithLeadingSlash()
	{
		// this test is behaving inconsistently in different environments
		$this->markTestSkipped('Store_Cdn relies on this in __construct which we cannot yet mock in PHPUnit versions we use');

		$input = '/foo/bar';
		$expected = Store_Asset::getBaseUrl(null, true) . '/foo/bar';
		$this->assertSame($expected, Store_Asset::generateUrl($input));
	}

	public function testGeneratePathWithSpace()
	{
		$input = 'foo bar';
		$expected = Store_Asset::getBasePath() . '/foo bar';
		$this->assertSame($expected, Store_Asset::generatePath($input));
	}

	/**
	 * @group flaky
	 */
	public function testGenerateUrlWithSpace()
	{
		// this test is behaving inconsistently in different environments
		$this->markTestSkipped('Store_Cdn relies on this in __construct which we cannot yet mock in PHPUnit versions we use');

		$input = 'foo bar';
		$expected = Store_Asset::getBaseUrl(null, true) . '/foo%20bar';
		$this->assertSame($expected, Store_Asset::generateUrl($input));
	}

	public function dataProviderFilterPathAgainstDataset()
	{
		$data = array();

		$data[] = array('', '');
		$data[] = array(false, '');

		// expected adjustments
		$data[] = array('foo\bar', 'foo/bar');
		$data[] = array('/foo/bar', '/foo/bar');
		$data[] = array('foo/./bar', 'foo/bar');
		$data[] = array('foo/../bar', 'foo/bar');
		$data[] = array('foo/.././bar', 'foo/bar');
		$data[] = array('foo/./../bar', 'foo/bar');
		$data[] = array('foo/././bar', 'foo/bar');
		$data[] = array('foo/../../bar', 'foo/bar');
		$data[] = array('foo/.bar', 'foo/.bar');
		$data[] = array('foo//bar', 'foo/bar');
		$data[] = array('/foo/bar/', '/foo/bar/');
		$data[] = array(' foo/bar ', 'foo/bar');
		$data[] = array("foo/\x00bar", 'foo/bar');
		$data[] = array("foo/:bar", 'foo/bar');
		$data[] = array("foo/>bar", 'foo/bar');
		$data[] = array("foo/<bar", 'foo/bar');
		$data[] = array("foo\r\nbar", 'foobar');
		$data[] = array('../foo/bar', 'foo/bar');
		$data[] = array('../../foo/bar', 'foo/bar');
		$data[] = array('../foo/../../bar', 'foo/bar');
		$data[] = array('./foo/bar', 'foo/bar');
		$data[] = array('/../foo/bar', '/foo/bar');
		$data[] = array('/./foo/bar', '/foo/bar');
		$data[] = array('~', '');
		$data[] = array('~/foo/bar', 'foo/bar');
		$data[] = array('.foo/../bar', '.foo/bar');
		$data[] = array('foo/bar/.', 'foo/bar');
		$data[] = array('foo/bar/..', 'foo/bar');

		// things that you might think should be, but actually shouldn't be adjusted
		$data[] = array("foo/#bar", 'foo/#bar');
		$data[] = array('foo/..bar', 'foo/..bar');
		$data[] = array('..foo/bar', '..foo/bar');
		$data[] = array('.../foo/bar', '.../foo/bar');
		$data[] = array('foo/.../bar', 'foo/.../bar');
		$data[] = array('foo/bar/...', 'foo/bar/...');
		$data[] = array('foo/bar..', 'foo/bar..');
		$data[] = array('foo/bar.php', 'foo/bar.php');
		$data[] = array('~foo/bar', '~foo/bar');
		$data[] = array('foo/~/bar', 'foo/~/bar');
		$data[] = array('foo/bar~~', 'foo/bar~~');

		// Things that should not happen, this is probably in no way an exhaustive/conclusive list.
		$data[] = array("./.\0./config.php", 'config.php');
		$data[] = array("./.*./config.php", 'config.php');
		$data[] = array("./.*./.*./config.php", 'config.php');
		$data[] = array("./.*./.*.///.*./.*.///.*./.*./config.php", 'config.php');
		$data[] = array("./.*./.*.///.\x7F./.*.///.*./.*./config.php", 'config.php');

		return $data;
	}

	/**
	 * @dataProvider dataProviderFilterPathAgainstDataset
	 * @param $input
	 * @param $expected
	 */
	public function testFilterPathAgainstDataset($input, $expected)
	{
		$this->assertSame($expected, Store_Asset::filterPath($input));
	}

	public function testAssetMethodsDoNotEncodeInternalPaths()
	{
		$input = 'directory/path with spaces.jpg';
		$expected = 'asset://file/directory/path with spaces.jpg';
		$this->assertSame($expected, Store_Asset::generatePath($input));
	}

	public function getGetStorageTypeByAssetPathData()
	{
		$data = array();

		$data[] = array('swift://foo', 'swift');
		$data[] = array('/var/foo', 'local');
		$data[] = array('', 'local');
		$data[] = array(false, 'local');
		$data[] = array(null, 'local');
		$data[] = array('http://', false);
		$data[] = array('blargh://', false);

		return $data;
	}

	/**
	 * @dataProvider getGetStorageTypeByAssetPathData
	 */
	public function testGetStorageTypeByAssetPath($input, $expected)
	{
		$this->assertSame($expected, Store_Asset::getStorageTypeByAssetPath($input));
	}
}
