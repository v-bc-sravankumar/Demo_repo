<?php

require_once('ArrayObjectTest.php');

use Arrays\RecursiveArrayObject;

class RecursiveArrayObjectTest extends ArrayObjectTest
{

	public function createArray()
	{
		return new RecursiveArrayObject();
	}

	public function tearDown()
	{
		parent::tearDown();
	}

}