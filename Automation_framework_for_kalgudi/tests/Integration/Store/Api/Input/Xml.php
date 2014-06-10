<?php

require_once dirname(__FILE__) . '/../Input.php';

class Unit_Lib_Store_Api_Input_Xml extends Unit_Lib_Store_Api_Input
{
	public function dataProvider()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<product>
  <id>1</id>
  <name>MacBook</name>
  <price>1999.99</price>
  <featured>true</featured>
  <categories>
	<value>2</value>
	<value>6</value>
	<value>10</value>
	<value>11</value>
  </categories>
  <details>
	<tax>54.21</tax>
	<stock>5</stock>
  </details>
  <related>
	<value>4</value>
  </related>
  <images>
	<image>
	  <id>6</id>
	  <file>foo.jpg</file>
	</image>
	<image>
	  <id>8</id>
	  <file>bar.png</file>
	</image>
  </images>
  <videos>
	<video>
		<id>10</id>
		<url>http://www.youtube.com/watch?foo</url>
	</video>
  </videos>
  <long_number>9102927002338349750071</long_number>
</product>
';

		$parser = new Store_Api_InputParser_Xml();
		$input = $parser->parseInput($xml);
		return array(array($input));
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testStringsOnlyConvertedToNumericIfNoPrecisionLost(Store_Api_Input $input)
	{
		$this->assertSame('9102927002338349750071', $input->long_number);
	}

	public function testIntCasting()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<product>
	<amount>50</amount>
</product>
		';

		$parser = new Store_Api_InputParser_Xml();
		$input = $parser->parseInput($xml);

		$this->assertTrue(is_int($input->amount));

	}

	public function testIntCastingWithLeadingZeros()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<product>
	<amount>00050</amount>
</product>
		';

		$parser = new Store_Api_InputParser_Xml();
		$input = $parser->parseInput($xml);

		$this->assertTrue(is_int($input->amount));
		$this->assertSame(50, $input->amount);

	}

	public function testIntCastingNegative()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<product>
	<amount>-50</amount>
</product>
		';

		$parser = new Store_Api_InputParser_Xml();
		$input = $parser->parseInput($xml);

		$this->assertTrue(is_int($input->amount));
		$this->assertEquals(-50, $input->amount);

	}

	public function testDoubleCasting()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<product>
	<amount>50.23</amount>
</product>
		';

		$parser = new Store_Api_InputParser_Xml();
		$input = $parser->parseInput($xml);

		$this->assertTrue(is_double($input->amount));
		$this->assertEquals(50.23, $input->amount);

	}

	public function testIntCastingWithTrailingZerosAfterDecimalPlace()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<product>
	<amount>50.00</amount>
</product>
		';

		$parser = new Store_Api_InputParser_Xml();
		$input = $parser->parseInput($xml);

		$this->assertTrue(is_int($input->amount));
		$this->assertEquals(50, $input->amount);

	}

	public function testIntCastingWithTrailingDecimalPlace()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<product>
	<amount>50.</amount>
</product>
		';

		$parser = new Store_Api_InputParser_Xml();
		$input = $parser->parseInput($xml);

		$this->assertFalse(is_int($input->amount));
		$this->assertFalse(is_double($input->amount));
		$this->assertTrue(is_string($input->amount));
		$this->assertEquals('50.', $input->amount);

	}

	public function testDoubleCastingWithTrailingZeros()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<product>
	<amount>5.53400</amount>
</product>
		';

		$parser = new Store_Api_InputParser_Xml();
		$input = $parser->parseInput($xml);

		$this->assertTrue(is_double($input->amount));
		$this->assertEquals(5.534, $input->amount);

	}

	public function testCastingWithInvalidNumberHex()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<product>
	<amount>0x539</amount>
</product>
		';

		$parser = new Store_Api_InputParser_Xml();
		$input = $parser->parseInput($xml);

		$this->assertFalse(is_double($input->amount));
		$this->assertFalse(is_int($input->amount));
		$this->assertTrue(is_string($input->amount));
		$this->assertEquals('0x539', $input->amount);

	}

	public function testCastingWithInvalidNumberBinary()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<product>
	<amount>0b10100111001</amount>
</product>
		';

		$parser = new Store_Api_InputParser_Xml();
		$input = $parser->parseInput($xml);

		$this->assertFalse(is_double($input->amount));
		$this->assertFalse(is_int($input->amount));
		$this->assertTrue(is_string($input->amount));
		$this->assertEquals('0b10100111001', $input->amount);

	}

}
