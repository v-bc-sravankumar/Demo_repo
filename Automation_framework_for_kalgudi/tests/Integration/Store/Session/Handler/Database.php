<?php

class Unit_Lib_Store_Session_Handler_Database extends Interspire_IntegrationTest
{
	private $_handler;

	public function setUp()
	{
		$this->_handler = new Store_Session_Handler_Database;
	}

	public function tearDown()
	{
	}

	public function testNonExistentSessionReturnsFalseWithExists()
	{
		$this->assertFalse($this->_handler->exists('non_existent_session'));
	}

	public function testExistentSessionReturnsTrueWithExists()
	{
		$this->_handler->set('existent_session', 'foo');
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
	}

	public function testGettingSessionReturnsData()
	{
		$this->assertTrue($this->_handler->set('test_get', '1234'));
		$this->assertEquals('1234', $this->_handler->get('test_get'));
	}

	public function testSessionCanBeDestroyed()
	{
		$this->_handler->set('test_destroy', 'foo');
		$this->assertTrue($this->_handler->destroy('test_destroy'));
		$this->assertFalse($this->_handler->exists('test_destroy'));
	}

	public function testSessionLastUpdatedChangesOnUpdate()
	{
		$this->_handler->set('test_update', 'foo');
		$lastUpdated = $this->fixtures->fetchOne("
			SELECT sesslastupdated
			FROM [|PREFIX|]sessions
			WHERE sessionhash='test_update'
		");
		$this->assertGreaterThan(0, $lastUpdated);
		sleep(2);
		$this->_handler->set('test_update', 'test');
		$newLastUpdated = $this->fixtures->fetchOne("
			SELECT sesslastupdated
			FROM [|PREFIX|]sessions
			WHERE sessionhash='test_update'
		");
		$this->assertGreaterThan($lastUpdated, $newLastUpdated);
	}

	public function testSessionGarbageCollection()
	{
		$this->_handler->set('test_destroy', 'foo');
		sleep(5);
		$this->_handler->set('test_destroy_2', 'foo');
		$this->_handler->garbageCollect(2);
		$this->assertFalse($this->_handler->exists('test_destroy', ''));
		$this->assertTrue($this->_handler->exists('test_destroy_2', ''));
	}

	public function testEmptySessionIsntPersisted()
	{
		$this->_handler->set('empty_session', '');
		$this->assertFalse($this->_handler->exists('empty_session'));
	}
}
