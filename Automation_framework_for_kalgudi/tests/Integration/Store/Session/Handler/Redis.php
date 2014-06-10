<?php

class Unit_Lib_Store_Session_Handler_Redis extends Interspire_IntegrationTest
{
	private $_handler;
	private $_redis;
	private $_namespace;

	protected function clearNamespace($namespace = null)
	{
		$success = true;
		$namespace = $namespace ?: $this->_namespace;
		$keys = $this->_redis->keys("$namespace*");

		if (is_array($keys)) {
			foreach ($keys as $key) {
				$success = $this->_redis->del($key) && $success;
			}
		}

		return $success;
	}

	public function setUp()
	{
		parent::setUp();

		$rand = bin2hex(openssl_random_pseudo_bytes(4));

		$this->_namespace = "session_test_:$rand";
		$this->_redis     = $GLOBALS["app"]["redis.client"];
		$this->_handler   = new Store_Session_Handler_Redis(array(
			"redis" => $this->_redis,
			"namespace" => $this->_namespace,
		));

		$this->clearNamespace();
	}

	public function tearDown()
	{
		$this->clearNamespace();
	}

	public function testNonExistentSessionReturnsFalseWithExists()
	{
		$this->assertFalse($this->_handler->exists('non_existent_session'));
	}

	public function testExistentSessionReturnsTrueWithExists()
	{
		$this->_handler->set('existent_session', '');
		$this->assertTrue($this->_handler->exists('existent_session'));
	}

	public function testSessionCanBeUpdated()
	{
		$this->assertTrue($this->_handler->set('test_updated', '1234'));
		$this->assertEquals('1234', $this->_handler->get('test_updated'));
		$this->assertTrue($this->_handler->set('test_updated', '5678'));
		$this->assertEquals('5678', $this->_handler->get('test_updated'));
	}

	public function testGettingNonExistentSessionReturnsFalse()
	{
		$this->assertFalse($this->_handler->get('non_existent_session'));
		$this->assertNull($this->_handler->getSessionHash());
	}

	public function testGettingSessionReturnsData()
	{
		$this->assertTrue($this->_handler->set('test_get', '1234'));
		$this->assertEquals('1234', $this->_handler->get('test_get'));
	}

	public function testSessionCanBeDestroyed()
	{
		$this->_handler->set('test_destroy', '');
		$this->assertTrue($this->_handler->destroy('test_destroy'));
		$this->assertFalse($this->_handler->exists('test_destroy'));
	}

	public function testSessionHasExpirySet()
	{
		$this->_handler->set('test_expiry', '');
		$key = $this->_handler->getNamespace() . 'test_expiry';
		$this->assertGreaterThan(0, $this->_redis->ttl($key));
	}

	public function testSessionExpiresAfterLifetime()
	{
		$this->_handler->setLifetime(1);
		$this->assertTrue($this->_handler->set('test_expiry', ''));

		// Sleep for 2 seconds - the session should be removed
		sleep(2);

		$this->assertFalse($this->_handler->exists('test_expiry'));
	}

	public function testUpdatedSessionHasNewExpiry()
	{
		$key = $this->_handler->getNamespace() . 'test_expiry';
		$this->_handler->setLifetime(500);
		$this->assertTrue($this->_handler->set('test_expiry', ''));
		$expiry = $this->_redis->ttl($key);
		$this->assertGreaterThan(0, $expiry);
		$this->assertLessThanOrEqual(500, $expiry);

		// perform a get() to set the session hash and make sure
		// expire() is used instread of setEx() internally
		$this->assertEquals('', $this->_handler->get('test_expiry'));

		$this->_handler->setLifetime(10000);
		$this->assertTrue((boolean)$this->_handler->set('test_expiry', ''));
		$this->assertGreaterThan(500, $this->_redis->ttl($key));
	}

	public function testGetAndSetLargeSession()
	{
		$lipsum = file_get_contents(TEST_DATA_ROOT . '/lipsum.txt');
		$expected = str_repeat($lipsum, 100); // Approx 12 MB
		$expectedMd5 = md5($expected);

		$this->_handler->set('test_large_session', $expected);
		$result = $this->_handler->get('test_large_session');
		$savedMd5 = md5($result);
		$this->assertEquals($expectedMd5, $savedMd5);
	}

	public function testNamespacedKeysDoNotInterfere()
	{
		// Set some data up in the first handler
		$handler = $this->_handler;
		$handler->set('key_1', 'handler_1');
		$handler->set('key_2', 'handler_1');
		$handler->set('key_3', 'handler_1');

		// Set up a second handler to check conflicts
		$customNamespace = 'foo_' . $this->_namespace;
		$handler2 = new Store_Session_Handler_Redis(array(
			'redis'     => $this->_redis,
			'namespace' => $customNamespace,
		));

		$handler2->set('key_1', 'handler_2');
		$handler2->set('key_2', 'handler_2');

		// Ensure read operations do not conflict
		$this->assertEquals('handler_1', $handler->get('key_1'));
		$this->assertEquals('handler_2', $handler2->get('key_1'));

		// Ensure that destroys do not conflict
		$handler->destroy('key_2');
		$this->assertEquals('handler_2', $handler2->get('key_2'));

		// Ensure that keys that exist in one, do not exist in the other
		$this->assertFalse($handler2->exists('key_3'));
		$this->assertFalse($handler2->get('key_3'));

		// clear the custom namespace
		$this->clearNamespace($customNamespace);
	}
}
