<?php

namespace Unit\Library\Encoding\Json;

use Store_Api_InputParser_Json;

class InputParserTest extends \PHPUnit_Framework_TestCase
{
	public function testParseValidInput()
	{
		$json = '{"first_name":"bob","last_name":"smith"}';
		$parser = new Store_Api_InputParser_Json();
		$input = $parser->parseInput($json);
		$this->assertInstanceOf('Store_Api_Input_Json', $input);
	}

	/**
	* @expectedException Store_Api_Exception_InputParser_Json_InvalidInput
	*/
	public function testParseInvalidInput()
	{
		$json = '{first_name:"bob"}';
		$parser = new Store_Api_InputParser_Json();
		$parser->parseInput($json);
	}
}
