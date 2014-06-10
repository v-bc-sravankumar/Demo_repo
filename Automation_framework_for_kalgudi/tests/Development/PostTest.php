<?php

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// remove these once the tests are ported to their
// rightful place
require_once dirname(__FILE__).'/../../general.php';
require_once dirname(__FILE__).'/../Exception.php';
require_once dirname(__FILE__).'/../Http.php';
require_once 'Request.php';
require_once 'Response.php';
require_once 'Client.php';
require_once 'RequestOptions.php';
require_once 'Exception.php';
require_once 'NetworkError.php';
require_once 'ProtocolError.php';
require_once 'ClientError.php';
require_once 'ServerError.php';
require_once 'RequestOptions.php';
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

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
 * Characterization test of existing functionality.
 *
 * Verify the behavior of this function so that we can change it's internals without changing the signature
 * or affectingcode that depends on it.
 *
 * @group remote
 */
class PostToRemoteFileAndGetResponse_Test extends PHPUnit_Framework_TestCase {

	const HOST = 'http://conformity.sourceforge.net';

	public function testGetRequest()
	{
		$url = self::HOST.'/basic/get';

		$log = $this->getMock('Interspire_Log_Mock');
		$log->expects($this->once())->method('LogSystemDebug');
		$GLOBALS['ISC_CLASS_LOG'] = $log;

		$result = PostToRemoteFileAndGetResponse($url);
		$this->assertContains("CANHAZHTTPGET", $result);
	}

	public function testPostRequest()
	{
		$url = self::HOST.'/basic/post';
		$vars = array("greeting"=>"Hello", "from"=>"PostToRemoteFileAndGetResponse");

		$log = $this->getMock('Interspire_Log_Mock');
		$log->expects($this->once())->method('LogSystemDebug');
		$GLOBALS['ISC_CLASS_LOG'] = $log;

		$result = PostToRemoteFileAndGetResponse($url, $vars);
		$this->assertContains("Hello back", $result);
	}

	public function testErrorOnNetworkTimeout()
	{
		$url = self::HOST.'/basic/errors/timeout';
		$vars = array("greeting"=>"Hello", "from"=>"PostToRemoteFileAndGetResponse");
		$timeout = 1;

		$log = $this->getMock('Interspire_Log_Mock');
		$log->expects($this->once())->method('LogSystemWarning');
		$GLOBALS['ISC_CLASS_LOG'] = $log;

		$result = PostToRemoteFileAndGetResponse($url, $vars, $timeout);

		$this->assertEquals(null, $result);
	}

	public function testErrorFromNotFoundResponse()
	{
		$url = self::HOST.'/basic/errors/missing';
		$vars = array("greeting"=>"Hello", "from"=>"PostToRemoteFileAndGetResponse");
		$timeout = 60;

		$log = $this->getMock('Interspire_Log_Mock');
		$log->expects($this->once())->method('LogSystemWarning');
		$GLOBALS['ISC_CLASS_LOG'] = $log;

		$result = PostToRemoteFileAndGetResponse($url, $vars, $timeout);

		$this->assertEquals(null, $result);
	}

	public function testErrorFromInternalErrorResponse()
	{
		$url = self::HOST.'/basic/errors/crash';
		$vars = array("greeting"=>"Hello", "from"=>"PostToRemoteFileAndGetResponse");
		$timeout = 60;

		$log = $this->getMock('Interspire_Log_Mock');
		$log->expects($this->once())->method('LogSystemWarning');
		$GLOBALS['ISC_CLASS_LOG'] = $log;

		$result = PostToRemoteFileAndGetResponse($url, $vars, $timeout);

		$this->assertEquals(null, $result);
	}

}