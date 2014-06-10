<?php
namespace Unit\Logging;

use Logging\Logger;
use Monolog\Handler\TestHandler;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
	/** @var TestHandler */
	private $testHandler;

	public function setUp()
	{
		$testHandler = new TestHandler();
		$this->testHandler = $testHandler;

		$logger = new \Monolog\Logger('BigcommerceTestLogger');
		$logger->pushHandler($testHandler);

		Logger::setInstance($logger);
	}

	public function testEmergency()
	{
		$msg = 'Emergency';
		Logger::emergency($msg);
		$this->assertTrue($this->testHandler->hasEmergency($msg));
	}

	public function testAlert()
	{
		$msg = 'Alert';
		Logger::alert($msg);
		$this->assertTrue($this->testHandler->hasAlert($msg));
	}

	public function testCritical()
	{
		$msg = 'Critical';
		Logger::critical($msg);
		$this->assertTrue($this->testHandler->hasCritical($msg));
	}

	public function testError()
	{
		$msg = 'Error';
		Logger::error($msg);
		$this->assertTrue($this->testHandler->hasError($msg));
	}

	public function testWarning()
	{
		$msg = 'Warning';
		Logger::warning($msg);
		$this->assertTrue($this->testHandler->hasWarning($msg));
	}

	public function testNotice()
	{
		$msg = 'Notice';
		Logger::notice($msg);
		$this->assertTrue($this->testHandler->hasNotice($msg));
	}

	public function testInfo()
	{
		$msg = 'Info';
		Logger::info($msg);
		$this->assertTrue($this->testHandler->hasInfo($msg));
	}

	public function testDebug()
	{
		$msg = 'Debug';
		Logger::debug($msg);
		$this->assertTrue($this->testHandler->hasDebug($msg));
	}

	public function testEnvConfig()
	{
		// Nullify logger to ensure env config is used
		Logger::setInstance(null);
		/** @var \Monolog\Logger $logger */
		$logger = Logger::getInstance();
		/** @var \Logging\MockHandler $handler */
		$handler = $logger->popHandler();
		$logger->pushHandler($handler);

		// Check that args supplied in config were passed to the handler's constructor
		$this->assertTrue($handler->hasRcvdArgs());

		$msg = 'TEST_MESSAGE';
		Logger::debug($msg);
		$this->assertTrue($handler->hasDebug($msg));
	}
}

