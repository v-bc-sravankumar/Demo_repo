<?php

use Store\Preference;
use Store\Preference\Driver\Dummy;

class Unit_Store_PreferenceTest extends PHPUnit_Framework_TestCase
{
	private $preference = null;
	public function setUp()
	{
		$this->preference = new Preference(new Dummy());
	}

	public function testDefine()
	{
		$this->preference->define('name', 'field', 'value');
		$this->assertTrue($this->preference->has('name', 'field'));
		$this->assertFalse($this->preference->has('something', 'else'));
	}

	public function testGetUndefinedValue()
	{
		$this->assertNull($this->preference->get('default', 'limit'));
	}

	public function testGetDefaultValue()
	{
		$this->preference->define('default', 'limit', 10);
		$this->assertEquals(10, $this->preference->get('default', 'limit'));
	}

	public function testSetUndefinedValue()
	{
		$this->preference->set('default', 'limit', 10);
		$this->assertNull($this->preference->get('default', 'limit'));
	}

	public function testPersistedValueAccessor()
	{
		$this->preference->define('default', 'limit', 10);
		$this->preference->set('default', 'limit', 20);
		$this->assertEquals(20, $this->preference->get('default', 'limit'));
	}

	public function testExplicitDefaultName()
	{
		$this->preference->setDefaultName('index');
		$this->assertEquals('index', $this->preference->getDefaultName());
	}

	public function testImplicitDefaultName()
	{
		$this->preference->define('first', 'limit', 10);
		$this->preference->define('second', 'limit', 20);
		$this->assertEquals('first', $this->preference->getDefaultName());
	}

}
