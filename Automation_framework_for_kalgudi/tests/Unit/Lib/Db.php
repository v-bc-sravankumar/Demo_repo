<?php

class Unit_Lib_Db extends PHPUnit_Framework_TestCase
{
	public function testSanitizeIntCastToValidInt()
	{
		$expected = array(1, 2, 1);
		$input = array(1, 2, '1 AND sleep');
		$db = new Db_Mysql();
		$this->assertEquals($expected, $db->sanitizeAsInt($input));
	}

	public function testSanitizeIntCastToZero()
	{
		$expected = array(1, 2, 0);
		$input = array(1, 2, 'AND sleep');
		$db = new Db_Mysql();
		$this->assertEquals($expected, $db->sanitizeAsInt($input));
	}
}
