<?php

namespace Unit\Library\Encoding\Xml;

use Store_Api_InputParser_Xml;

class InputParserTest extends \PHPUnit_Framework_TestCase
{
	public function testParseValidInput()
	{
		$xml = '<?xml version="1.0"?>
<customer>
	<first_name>bob</first_name>
	<last_name>smith</last_name>
</customer>';
		$parser = new Store_Api_InputParser_Xml();
		$input = $parser->parseInput($xml);
		$this->assertInstanceOf('Store_Api_Input_Xml', $input);
	}

	/**
	* @expectedException Store_Api_Exception_InputParser_Xml_InvalidInput
	*/
	public function testParseInvalidInput()
	{
		$xml = "foo";
		$parser = new Store_Api_InputParser_Xml();
		$parser->parseInput($xml);
	}
}
