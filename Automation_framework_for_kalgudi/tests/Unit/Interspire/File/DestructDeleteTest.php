<?php

// can't think of a better way of testing this -- I can't use vfsStream
class Test_Lib_Interspire_File_DestructDelete_StreamWrapper
{
	public static $unlinks = array();

	public function unlink ($path)
	{
		self::$unlinks[] = $path;
		return true;
	}

	public function url_stat ($path)
	{
		return array(
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
		);
	}
}

class Unit_Lib_Interspire_File_DestructDeleteTest extends PHPUnit_Framework_TestCase
{
	public function setUp ()
	{
		stream_wrapper_register('destructdelete', 'Test_Lib_Interspire_File_DestructDelete_StreamWrapper');
		Test_Lib_Interspire_File_DestructDelete_StreamWrapper::$unlinks = array();
	}

	public function tearDown ()
	{
		stream_wrapper_unregister('destructdelete');
	}

	private function _instantiateDestructDelete ($path, $cancel = false)
	{
		$destruct = new Interspire_File_DestructDelete($path);

		if ($cancel) {
			$destruct->cancel();
		}
	}

	public function testFileDeletedWhenOutOfScope ()
	{
		$random = 'destructdelete://' . mt_rand(0, PHP_INT_MAX);
		$this->_instantiateDestructDelete($random);

		$this->assertSame(1, count(Test_Lib_Interspire_File_DestructDelete_StreamWrapper::$unlinks), "unlink count mismatch");

		$unlink = array_pop(Test_Lib_Interspire_File_DestructDelete_StreamWrapper::$unlinks);
		$this->assertSame($random, $unlink, "unlink path mismatch");
	}

	public function testCancelledDestructDoesntDelete ()
	{
		$random = 'destructdelete://' . mt_rand(0, PHP_INT_MAX);
		$this->_instantiateDestructDelete($random, true);

		$this->assertSame(0, count(Test_Lib_Interspire_File_DestructDelete_StreamWrapper::$unlinks), "unlink count mismatch");
	}
}