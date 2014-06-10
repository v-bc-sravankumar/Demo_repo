<?php

define("ISC_TMP_IMPORT_DIRECTORY", BUILD_ROOT . "cache/import");
if (!is_dir(ISC_TMP_IMPORT_DIRECTORY)) {
	mkdir(ISC_TMP_IMPORT_DIRECTORY, 0777, true);
}

require_once('ImportSessionTest.php');
use Store\Import\Session;

class Unit_Lib_Store_Import_ImportSessionFileTest extends Unit_Lib_Store_Import_ImportSessionTest
{

	public function setUp()
	{
		Session::setUseRedisDriver(false);
		parent::setUp();
	}

	public function tearDown()
	{
		parent::tearDown();
		Session::setUseRedisDriver(true);
	}

}