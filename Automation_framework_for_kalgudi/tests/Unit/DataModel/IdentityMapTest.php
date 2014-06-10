<?php

class ExampleEntity
{
	private $id;

	public function __construct($id)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}
}

class IdentityMapTest extends PHPUnit_Framework_TestCase
{
	public function testCanPutAndGetFromMap()
	{
		$eg1 = new ExampleEntity(1);
		$eg2 = new ExampleEntity(2);

		\DataModel\IdentityMap::putEntity($eg1);
		\DataModel\IdentityMap::putEntity($eg2);

		$this->assertTrue(\DataModel\IdentityMap::containsEntity('ExampleEntity', 1));
		$this->assertTrue(\DataModel\IdentityMap::containsEntity('ExampleEntity', 2));
		$this->assertFalse(\DataModel\IdentityMap::containsEntity('ExampleEntity', 3));

		$this->assertSame($eg1, \DataModel\IdentityMap::loadEntity('ExampleEntity', 1));
		$this->assertSame($eg2, \DataModel\IdentityMap::loadEntity('ExampleEntity', 2));
	}
}