<?php

require_once('DelayedRedisArrayTest.php');

use RedisArray\DelayedRedisArray;
use RedisArray\IndexedKeysRedisArray;

class DelayedIndexedRedisArrayTest extends DelayedRedisArrayTest
{

	public function createArray()
	{
		$credis = new \Predis\Client();
		$credis->select(10);
		return new DelayedRedisArray(new IndexedKeysRedisArray("__DelayedRedisArrayTests::array__", $credis));
	}

}