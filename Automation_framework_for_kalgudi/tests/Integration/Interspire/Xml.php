<?php

class Unit_Lib_Xml extends Interspire_IntegrationTest
{
	private function getDataElement($rootTag = 'data')
	{
		return Interspire_Xml::createXML($rootTag);
	}

	public function testCreateXmlElement()
	{
		$xml = $this->getDataElement();
		$out = $xml->asXML();

		$expected = Interspire_Xml::getDeclaration();
		$expected .= '
<data/>
';

		$this->assertEquals($expected, $out);
	}

	public function testArray2XmlNormal()
	{
		// normal array
		$input = array(
			'status' => 1,
			'message' => 'success',
		);

		$xml = $this->getDataElement();
		Interspire_Xml::addArrayToXML($xml, $input);
		$out = $xml->asXML();

		$expected = Interspire_Xml::getDeclaration();
		$expected .= '
<data><status>1</status><message>success</message></data>
';

		$this->assertEquals($expected, $out);
	}

	public function testArray2XmlSpecialCase()
	{
		// _processValue()
		$input = array(
			'true' => true,
			'false' => false,
			'null' => null,
			'zero' => 0,
			'zeroStr' => '0',
			'numeric' => 1234.56,
			'object' => simplexml_load_string('<root/>'),
		);

		$xml = $this->getDataElement();
		Interspire_Xml::addArrayToXML($xml, $input);

		$out = Interspire_Xml::prettyIndent($xml);

		$expected = Interspire_Xml::getDeclaration();
		$expected .= '
<data>
  <true>1</true>
  <false>0</false>
  <null>0</null>
  <zero>0</zero>
  <zeroStr>0</zeroStr>
  <numeric>1234.56</numeric>
  <object>0</object>
</data>
';
		$this->assertEquals($expected, $out);
	}


	public function testArray2XmlNested()
	{
		// nested array with different indexes
		$in = array(
			'assoc' => array(
				'a' => 'alpha',
				'b' => 'beta',
			),
			'numerics' => array(
				'numeric' => array(
					0 => 'version 5.5',
					1 => 'version 5.6',
					2 => array(
						'beta' => '6',
					),
				)
			),
			'message' => array(
				'success',
				'error',
			),
		);

		$xml = $this->getDataElement();
		Interspire_Xml::addArrayToXML($xml, $in);
		$out = Interspire_Xml::prettyIndent($xml);

		$expected = Interspire_Xml::getDeclaration();
		$expected .= '
<data>
  <assoc>
    <a>alpha</a>
    <b>beta</b>
  </assoc>
  <numerics>
    <numeric>version 5.5</numeric>
    <numeric>version 5.6</numeric>
    <numeric>
      <beta>6</beta>
    </numeric>
  </numerics>
  <message>success</message>
  <message>error</message>
</data>
';
		$this->assertEquals($expected, $out);

		// reverse
/* @todo update the xml2array function
		$xml = simplexml_load_string($out);
		$result = Interspire_Xml::xml2array($xml);
		$this->assertEquals($in, $result);
*/
	}


	public function testArray2XmlAttributes()
	{
		$in = array(
			'customer' => array(
				0 => array(
					'@attributes' => array(
						'gender' => 'female',
					),
					'@value' => array(
						'firstname' => 'Jane',
						'lastname' => 'Public',
					),
				),
				1 => array(
					'@attributes' => array(
						'gender' => 'male',
						'creditcard' => 1, // note: don't use 'true'
					),
					'@value' => array(
						'firstname' => 'John',
						'lastname' => 'Citizen',
						'address' => array(
							'street' => 'Main Street',
							'city' => 'Awesome City',
							'country' => 'Beautiful Country',
							'postcode' => 1234,
						),
					),
				),
			),
		);

		$xml = $this->getDataElement('customers');
		Interspire_Xml::addArrayToXML($xml, $in);
		$out = Interspire_Xml::prettyIndent($xml);

		$expected = Interspire_Xml::getDeclaration();
		$expected .= '
<customers>
  <customer gender="female">
    <firstname>Jane</firstname>
    <lastname>Public</lastname>
  </customer>
  <customer gender="male" creditcard="1">
    <firstname>John</firstname>
    <lastname>Citizen</lastname>
    <address>
      <street>Main Street</street>
      <city>Awesome City</city>
      <country>Beautiful Country</country>
      <postcode>1234</postcode>
    </address>
  </customer>
</customers>
';
		$this->assertEquals($expected, $out);

		// reverse
		/*
		$xml = simplexml_load_string($out);
		$result = Interspire_Xml::xml2array($xml, 'customer');
		$this->assertEquals($in, $result);
		*/

		// scalar
		/*
		$in = array(
			array(
				'@attributes' => array(
					'gender' => 'male',
				),
				'@value' => 'John Citizen',
			),
			array(
				'@attributes' => array(
					'gender' => 'female',
				),
				'@value' => 'Jane & Citizen',
			),
		);
		$xml = $this->getDataElement('customers');
		Interspire_Xml::addArrayToXML($xml, $in);
		$out = Interspire_Xml::prettyIndent($xml);

		$expected = Interspire_Xml::getDeclaration();
		$expected .= '
<customers>
  <customer gender="male">John Citizen</customer>
  <customer gender="female"><![CDATA[Jane & Citizen]]></customer>
</customers>
';
		$this->assertEquals($expected, $out);
		*/

		// reverse
		/*
		$xml = simplexml_load_string($out);
		$result = Interspire_Xml::xml2array($xml, 'customer');
		$this->assertEquals($in, $result);
		*/
	}

	public function testXml2Array()
	{
		$in = '<hello>world</hello>';
		$xml = simplexml_load_string($in);
		$out = Interspire_Xml::xml2array($xml);
		$expected = array(
			'hello' => 'world',
		);
		$this->assertEquals($expected, $out);

		$in = '<hello say="1">world</hello>';
		$xml = simplexml_load_string($in);
		$out = Interspire_Xml::xml2array($xml);
		$expected = array(
			'@attributes' => array(
				'say' => 1,
			),
			'@value' => 'world',
		);
		$this->assertEquals($expected, $out);

		$in = '<data><hello>world</hello><hi>friend</hi></data>';
		$xml = simplexml_load_string($in);
		$out = Interspire_Xml::xml2array($xml);
		$expected = array(
			'hello' => 'world',
			'hi' => 'friend',
		);
		$this->assertEquals($expected, $out);

		// example of no flatten -> overwrite, default index is 'item'
		$in = '<data><hello>world</hello><hello>friend</hello></data>';
		$xml = simplexml_load_string($in);
		$out = Interspire_Xml::xml2array($xml);
		$expected = array(
			'hello' => 'friend',
		);
		$this->assertEquals($expected, $out);

		// change tag name to item, works
		$in = '<data><item>world</item><item>friend</item></data>';
		$xml = simplexml_load_string($in);
		$out = Interspire_Xml::xml2array($xml);
		$expected = array(
			'world',
			'friend',
		);
		$this->assertEquals($expected, $out);

		// specify tag name to flatten is hello
		$in = '<data><hello>world</hello><hello>friend</hello></data>';
		$xml = simplexml_load_string($in);
		$out = Interspire_Xml::xml2array($xml, 'hello');
		$expected = array(
			'world',
			'friend',
		);
		$this->assertEquals($expected, $out);
	}


	public function testSpecialChar()
	{
		// should create a cdata tag if there is a special char
		$special = array(
			'symbol' => '! @ # $ % ^ & * ( ) _ + - = [ ] \ { } | ; \' : " , . / < > ? &amp; &lt; &#039;',
			'html' => '<body><a href="http://example.com">click</a></body>',
			'space' => "white \t\n   space", // no cdata tag for this
			'raw' => '<span>comm]]>ent</span>', // premature end cdata tag will be escaped
		);

		$xml = $this->getDataElement();
		Interspire_Xml::addArrayToXML($xml, $special);
		$out = Interspire_Xml::prettyIndent($xml);

		$expected = Interspire_Xml::getDeclaration();
		$expected .= '
<data>
  <symbol><![CDATA['.$special['symbol'].']]></symbol>
  <html><![CDATA['.$special['html'].']]></html>
  <space>'.$special['space'].'</space>
  <raw><![CDATA[<span>comm]]]]><![CDATA[>ent</span>]]></raw>
</data>
';
		$this->assertTrue(Interspire_Xml::validateXMLString($out));
		$this->assertEquals($expected, $out);

		// reverse
		$xml = simplexml_load_string($out);
		$result = Interspire_Xml::xml2array($xml);
		$this->assertEquals($special, $result);

		// test all html entities
		$entities = array();
		foreach (get_html_translation_table(HTML_ENTITIES) as $entity => $encode) {
			$key = str_replace(array('&', ';'), '', $encode);
			$entities[$key] = $encode;
		}

		$xml = $this->getDataElement();
		Interspire_Xml::addArrayToXML($xml, $entities);
		$out = Interspire_Xml::prettyIndent($xml);

		$this->assertTrue(Interspire_Xml::validateXMLString($out));
		$xml = simplexml_load_string($out);
		$result = Interspire_Xml::xml2array($xml);
		$this->assertEquals($entities, $result);
	}


	public function testGetResponse()
	{
		$in = array(
			'status' => 1,
			'message' => 'success',
		);
		$result = Interspire_Xml::getResponse($in);
		$expected = Interspire_Xml::getDeclaration();
		$expected .= '
<response>
  <status>1</status>
  <message>success</message>
</response>
';
		$this->assertEquals($expected, $result);

		// wrong input
		$expected = Interspire_Xml::getDeclaration();
		$expected .= '
<response/>
';
		$result = Interspire_Xml::getResponse(array());
		$this->assertEquals($expected, $result);
	}


	public function testValidateXMLString()
	{
		// ok
		$valid = '<hello>world</hello>';
		$this->assertTrue(Interspire_Xml::validateXMLString($valid));

		// white space in tag name
		$error = '';
		$spaceInTag = '<hello world>failed</hello world>';
		$this->assertFalse(Interspire_Xml::validateXMLString($spaceInTag, $error));
		$expected = 'Specification mandate value for attribute world
expected \'>\'
Extra content at the end of the document
';
		$this->assertEquals($expected, $error);

		// address type attribute not closed
		$attrNotClosed = '<address type="work>
	  <road>Work building, work road</road>
	  <city>Work city</city>
	  <state>Work state</state>
	  <zipcode>12347</zipcode>
	  <country>USA</country>
	</address>';
		$this->assertFalse(Interspire_Xml::validateXMLString($attrNotClosed));
	}


	public function testEscapeCdata()
	{
		$in = 'abc]]>def';
		$expected = 'abc]]]]><![CDATA[>def';
		$out = Interspire_Xml::escapeCdata($in);
		$this->assertEquals($expected, $out);
	}
}
