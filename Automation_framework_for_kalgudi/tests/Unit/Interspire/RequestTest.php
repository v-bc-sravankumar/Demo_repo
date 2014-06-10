<?php

class Unit_Interspire_RequestTest extends PHPUnit_Framework_TestCase
{
	public function testGetReferer()
	{
		$request = new Interspire_Request(
			array(),
			array(),
			array(),
			array(
				'HTTP_REFERER' => 'http://www.google.com/',
			)
		);

		$this->assertSame('http://www.google.com/', $request->getReferer());
	}

	public function testGetRefererFalse()
	{
		$request = new Interspire_Request();
		$this->assertFalse($request->getReferer());
	}

	public function testIsCrawlerAgent()
	{
		$request = new Interspire_Request(
			array(),
			array(),
			array(),
			array(
				'HTTP_USER_AGENT' => 'Googlebot/2.1 (+http://www.googlebot.com/bot.html)',
			)
		);
		$this->assertTrue($request->isCrawlerAgent());
	}

	public function testGetCrawlerAgent()
	{
		$request = new Interspire_Request(
			array(),
			array(),
			array(),
			array(
				'HTTP_USER_AGENT' => 'Googlebot/2.1 (+http://www.googlebot.com/bot.html)',
			)
		);
		$this->assertSame('Googlebot/2.1 (+http://www.googlebot.com/bot.html)', $request->getUserAgent());
	}

	public function testIsCrawlerAgentReturnsFalse()
	{
		$request = new Interspire_Request(
			array(),
			array(),
			array(),
			array(
				'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36',
			)
		);
		$this->assertFalse($request->isCrawlerAgent());

		$request = new Interspire_Request();
		$this->assertFalse($request->isCrawlerAgent());
	}

	public function testAuthProductionHeaders ()
	{
		$user = 'foo';
		$pass = 'bar';
		$auth = 'Basic ' . base64_encode($user . ':' . $pass);

		$request = new Interspire_Request(
			array(),
			array(),
			array(),
			array(
				'HTTP_AUTHORIZATION' => $auth,
			)
		);

		$this->assertSame($user, $request->getAuthUser());
		$this->assertSame($pass, $request->getAuthPassword());
	}

	public function testGetContentType()
	{
		$request = new Interspire_Request(
			array(),
			array(),
			array(),
			array(
				'CONTENT_TYPE' => 'application/json; charset=utf8',
			)
		);

		$this->assertSame('application/json', $request->getContentType());
	}

	public function testGetAuthSchemeReturnsBasicForBasicAuth()
	{
		$request = new Interspire_Request(
			array(),
			array(),
			array(),
			array(
				'HTTP_AUTHORIZATION' => 'Basic YWJjOnh5eg==',
			)
		);

		$this->assertEquals('Basic', $request->getAuthScheme());
	}

	public function testGetAuthSchemeReturnsHawkForHawkAuth()
	{
		$request = new Interspire_Request(
			array(),
			array(),
			array(),
			array(
				'HTTP_AUTHORIZATION' => 'Hawk id="1" ts="1" nonce="x" mac="a"',
			)
		);

		$this->assertEquals('Hawk', $request->getAuthScheme());
	}

	public function testGetAuthSchemeDefaultsToEmptyString()
	{
		$request = new Interspire_Request();

		$this->assertEquals('', $request->getAuthScheme());
	}
}
