<?php

class Unit_Interspire_DateTime extends PHPUnit_Framework_TestCase
{
	public function testValidRfc2822Date()
	{
		$now = new DateTime();
		$strDate = $now->format(DateTime::RFC2822);
		$result = Interspire_DateTime::parseRfc2822Date($strDate);
		$this->assertEquals($now->getTimestamp(), $result->getTimestamp());
	}

	public function testInvalidRfc2822Date()
	{
		$result = Interspire_DateTime::parseRfc2822Date("Beer O'clock");
		$this->assertFalse($result);
	}

	public function testValidIso8601Date()
	{
		$now = new DateTime();
		$strDate = $now->format(DateTime::ISO8601);
		$result = Interspire_DateTime::parseIso8601Date($strDate);
		$this->assertEquals($now->getTimestamp(), $result->getTimestamp());
	}

	public function testInvalidIso8601Date()
	{
		$result = Interspire_DateTime::parseIso8601Date("Beer O'clock");
		$this->assertFalse($result);
	}

	public function testEitherIso8601OrRfc2822Date()
	{
		$now = new DateTime();

		$strRfcDate = $now->format(DateTime::RFC2822);
		$rfcResult = Interspire_DateTime::parse($strRfcDate);
		$this->assertEquals($now->getTimestamp(), $rfcResult->getTimestamp());

		$strIsoDate = $now->format(DateTime::ISO8601);
		$isoResult = Interspire_DateTime::parse($strIsoDate);
		$this->assertEquals($now->getTimestamp(), $isoResult->getTimestamp());
	}

	public function testNeitherIso8601OrRfc2822Date()
	{
		$result = Interspire_DateTime::parse("Beer O'clock");
		$this->assertFalse($result);
	}
}