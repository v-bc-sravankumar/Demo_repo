<?php

/**
 * @group remote
 */
class Unit_Lib_Interspire_Http_Options extends PHPUnit_Framework_TestCase {

	const HOST = 'http://conformity.sourceforge.net';

	public function testCanSetGlobalTimeoutOption()
	{
		// skipping this test due to flakyness on the CI server
		// @TODO attn: mark rickerby
		$this->markTestSkipped();
		return;

		Interspire_Http::setTimeout(1);

		try {
			$clientOne = new Interspire_Http_Client();
			$clientOne->get(self::HOST.'/basic/errors/timeout');
		} catch(Interspire_Http_NetworkError $e) {
			$this->assertContains("timed out", $e->getMessage());
			$this->assertEquals(28, $e->getCode());
		}
	}

	public function tearDown()
	{
		Interspire_Http::clearOptions();
	}

}