<?php

require_once('ImportSessionTest.php');

use Store\Import\Session;

class Unit_Lib_Store_Import_ImportSessionRedisTest extends Unit_Lib_Store_Import_ImportSessionTest
{
	protected static $defaultRedisImportSessionEnabled = null;

	public function setUp()
	{
		Session::setUseRedisDriver(true);

		parent::setUp();
	}

	public function tearDown()
	{
		parent::tearDown();
	}
}

