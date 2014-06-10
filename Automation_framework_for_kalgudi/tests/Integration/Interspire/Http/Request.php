<?php

/**
 * @group remote
 */
class Unit_Lib_Interspire_Http_Request extends PHPUnit_Framework_TestCase {

	const HOST = 'http://conformity.sourceforge.net';

	public function testGetRequest()
	{
		$url = self::HOST.'/basic/get';

		$request = new Interspire_Http_Request('get', $url);

		$this->assertEquals($request->getUrl(), $url);
		$this->assertEquals($request->getMethod(), 'GET');
	}

	public function testPostRequest()
	{
		$url = self::HOST.'/basic/post';
		$vars = array("greeting"=>"Hello", "from"=>"Interspire_Http_Request");

		$request = new Interspire_Http_Request('post', $url);
		$request->setParameters($vars);

		$this->assertEquals($request->getUrl(), $url);
		$this->assertEquals($request->getMethod(), 'POST');
	}

}