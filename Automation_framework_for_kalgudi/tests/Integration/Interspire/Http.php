<?php

if (!class_exists('Interspire_Log_Mock')) {
	// do nothing as this is a stub for running in tests
	class Interspire_Log_Mock {

		public function LogSystemDebug()
		{
			return true;
		}

		public function LogSystemWarning()
		{
			return true;
		}
	}
}

/**
 * @group remote
 */
class Unit_Lib_Interspire_Http extends PHPUnit_Framework_TestCase {

	const HOST = 'http://conformity.sourceforge.net';

	static public $logger;

	public function setUp()
	{
		if (isset($GLOBALS['ISC_CLASS_LOG'])) {
			self::$logger = $GLOBALS['ISC_CLASS_LOG'];
		}
	}

	public function tearDown()
	{
		if (self::$logger) {
			$GLOBALS['ISC_CLASS_LOG'] = self::$logger;
		}
	}

	public function testGetRequest()
	{
		$url = self::HOST.'/basic/get';

		$log = $this->getMock('Interspire_Log_Mock');
		$log->expects($this->once())->method('LogSystemDebug');
		$GLOBALS['ISC_CLASS_LOG'] = $log;

		$result = Interspire_Http::sendRequest($url);
		$this->assertContains("CANHAZHTTPGET", $result);
	}

	public function testPostRequest()
	{
		$url = self::HOST.'/basic/post';
		$vars = array("greeting"=>"Hello", "from"=>"Interspire_Http");

		$log = $this->getMock('Interspire_Log_Mock');
		$log->expects($this->once())->method('LogSystemDebug');
		$GLOBALS['ISC_CLASS_LOG'] = $log;

		$result = Interspire_Http::sendRequest($url, $vars);
		$this->assertContains("Hello back", $result);
	}

	public function testErrorOnNetworkFault()
	{
		$url = 'http://can.haz/bad/domain';
		$vars = array("greeting"=>"Hello", "from"=>"Interspire_Http");
		$curlError = CURLE_COULDNT_RESOLVE_HOST;
		$expectedError = "Interspire_Http_NetworkError: [$curlError] Couldn't resolve host 'can.haz'";

		$log = $this->getMock('Interspire_Log_Mock');
		$log->expects($this->once())->method('LogSystemWarning')->with('general', $expectedError);
		$GLOBALS['ISC_CLASS_LOG'] = $log;

		$result = Interspire_Http::sendRequest($url, $vars);

		$this->assertEquals(null, $result);
	}

	public function testErrorFromNotFoundResponse()
	{
		$url = self::HOST.'/basic/errors/missing';
		$vars = array("greeting"=>"Hello", "from"=>"Interspire_Http");

		$log = $this->getMock('Interspire_Log_Mock');
		$log->expects($this->once())->method('LogSystemWarning');
		$GLOBALS['ISC_CLASS_LOG'] = $log;

		$result = Interspire_Http::sendRequest($url, $vars);

		$this->assertEquals(null, $result);
	}

	public function testErrorFromInternalErrorResponse()
	{
		$url = self::HOST.'/basic/errors/crash';
		$vars = array("greeting"=>"Hello", "from"=>"Interspire_Http");

		$log = $this->getMock('Interspire_Log_Mock');
		$log->expects($this->once())->method('LogSystemWarning');
		$GLOBALS['ISC_CLASS_LOG'] = $log;

		$result = Interspire_Http::sendRequest($url, $vars);

		$this->assertEquals(null, $result);
	}
}