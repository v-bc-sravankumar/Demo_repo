<?php

namespace Unit\Library\Encoding;

use Store_Api_InputParser as InputParser;

class InputParserTest extends \PHPUnit_Framework_TestCase
{
	public function testGetParserForJson()
	{
		$encoder = InputParser::getInputParserForContentType("application/json");
		$this->assertInstanceOf("Store_Api_InputParser_Json", $encoder);
	}

	public function testGetParserForXml()
	{
		$encoder = InputParser::getInputParserForContentType("application/xml");
		$this->assertInstanceOf("Store_Api_InputParser_Xml", $encoder);
	}

	/**
	 * @expectedException Store_Api_Exception_Request_InvalidContentType
	 */
	public function testGetEncoderForInvalidContentType()
	{
		$this->assertFalse(InputParser::getInputParserForContentType('foo'));
	}
}
