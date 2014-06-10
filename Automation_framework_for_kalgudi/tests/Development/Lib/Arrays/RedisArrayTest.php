<?php

require_once('ArrayObjectTest.php');

use RedisArray\RedisArray;

class RedisArrayTest extends ArrayObjectTest
{

	public function createArray()
	{
		$client = new \Predis\Client();
		$client->select(10);
		return new RedisArray("__RedisArrayTests::array__", $client);
	}

	public function tearDown()
	{
		$this->array->delete();
		parent::tearDown();
	}

}