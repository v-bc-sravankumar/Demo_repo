<?php

class Unit_Lib_Redirect extends Interspire_IntegrationTest
{

	public function testNormalizeUrlForDatabase()
	{
		require_once BUILD_ROOT.'/admin/init.php';
		$engine = getClass('ISC_ADMIN_ENGINE');
		$engine->loadLangFile('redirects');

		// BCSIXBETA-231, ISC-1074, ISC-1202
		require_once ISC_BASE_PATH.'/lib/class.redirects.php';
		$errors = array();
		$results = array();
		$urls = array(
			'http://www.example.com/products/Another-Product-2386.html',
			'http://www.example.com/products/Product-3930.html',
			'http://www.example.com/page.php?id=235',
			'http://www.example.com/category.php?id=23&page=2',
			'http://www.example.com/category.php?id=20',
			'http://example.aspx',
			'http://example.aspx/test',
			'http://example.aspx/test/p1',
			'example.aspx',
			'/example.aspx',
			'example.aspx/test/product1',
			'filename.ext',
			'/filename.ext',
			'/one/%two%',
			);
		$expected = array(
			'/products/Another-Product-2386.html',
			'/products/Product-3930.html',
			'/page.php?id=235',
			'/category.php?id=23&page=2',
			'/category.php?id=20',
			'/',
			'/test',
			'/test/p1',
			'/example.aspx',
			'/example.aspx',
			'/example.aspx/test/product1',
			'/filename.ext',
			'/filename.ext',
			'/one/%two%',
		);

		foreach ($urls as $url) {
			$error = '';
			$results[] = ISC_REDIRECTS::normalizeURLForDatabase($url, $error);
			if (!empty($error)) {
				$errors[] = $error . "($url)";
			}
		}

		$this->assertEquals(array(), $errors);
		$this->assertEquals($expected, $results);
	}

	public function testNormalizeUrlForDatabaseExceedsMaxLength()
	{
		$error = '';
		$url = implode('', range(1, 378));
		ISC_REDIRECTS::normalizeURLForDatabase($url, $error);
		$this->assertEquals(GetLang('OldURLInvalid'), $error);
	}

	public function testNormalizeUrlForDatabaseInvalidUrl()
	{
		// invalid url
		$error = '';
		ISC_REDIRECTS::normalizeURLForDatabase('http:///example.com', $error);
		$this->assertEquals(GetLang('OldURLInvalid'), $error);
	}

	public function testNormalizeUrlForDatabaseStripAppPath()
	{
		// test strip app path
		$error = '';
		$result = ISC_REDIRECTS::normalizeURLForDatabase(getConfig('AppPath').'/test', $error);
		$this->assertEquals('/test', $result);
		$this->assertEquals('', $error);
	}

}
