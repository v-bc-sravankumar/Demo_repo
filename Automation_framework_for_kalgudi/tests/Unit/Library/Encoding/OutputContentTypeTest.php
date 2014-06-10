<?php

namespace Unit\Library\Encoding;

use Store_Api_OutputEncoder;

class OutputContentTypeTest extends \PHPUnit_Framework_TestCase
{
	public function testGetOutputEncoders()
	{
		$this->assertNotEmpty(Store_Api_OutputEncoder::getOutputEncoders());
	}

	public function getEncoderForDataTypeDataProvider()
	{
		$dataTypes = array(
			array('json'),
			array('xml'),
		);

		return $dataTypes;
	}

	/**
	* @dataProvider getEncoderForDataTypeDataProvider
	*/
	public function testGetEncoderForDataType($dataType)
	{
		$encoder = Store_Api_OutputEncoder::getOutputEncoderForDataType($dataType);
		$this->assertNotEmpty($encoder);
		$this->assertEquals($dataType, $encoder->getDataType());
	}

	public function testGetEncoderForInvalidDataType()
	{
		$this->assertFalse(Store_Api_OutputEncoder::getOutputEncoderForDataType('foo'));
	}

	public function getEncoderForContentTypeDataProvider()
	{
		$dataTypes = array(
			array('application/json'),
			array('application/xml'),
		);

		return $dataTypes;
	}

	/**
	* @dataProvider getEncoderForContentTypeDataProvider
	*/
	public function testGetEncoderForContentType($contentType)
	{
		$encoder = Store_Api_OutputEncoder::getOutputEncoderForContentTypes($contentType);
		$this->assertNotEmpty($encoder);
		$this->assertEquals($contentType, $encoder->getContentType());
	}

	public function testGetEncoderForInvalidContentType()
	{
		$this->assertFalse(Store_Api_OutputEncoder::getOutputEncoderForContentTypes('foo'));
	}
}
