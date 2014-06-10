<?php

class Functional_Database_MySql extends Interspire_UnitTest
{
	/**
	 * @link https://jira.bigcommerce.com/browse/ISC-4790
	 */
	public function testConnectionCharacterSetIsConfigured()
	{
		$dbEncoding = strtolower(Store_Config::get('dbEncoding'));

		$query = "SHOW VARIABLES LIKE 'character_set_connection'";
		$res = $GLOBALS['ISC_CLASS_DB']->Query($query);
		$var = $GLOBALS['ISC_CLASS_DB']->Fetch($res);
		$characterSet = strtolower($var['Value']);

		$this->assertEquals($dbEncoding, $characterSet);
	}

	/**
	 * @link https://jira.bigcommerce.com/browse/ISC-4790
	 */
	public function testWriteReadUtf8Data()
	{
		// get the current character set
		$query = "SHOW VARIABLES LIKE 'character_set_connection'";
		$res = $GLOBALS['ISC_CLASS_DB']->Query($query);
		$var = $GLOBALS['ISC_CLASS_DB']->Fetch($res);
		$currentCharacterSet = $var['Value'];

		// now ensure we're using utf8 (which we should be...) so we can write the data in utf8.
		$GLOBALS['ISC_CLASS_DB']->Query("SET NAMES utf8");

		// store some utf-8 data
		$string = "いろはにほへど";
		$keystore = Interspire_KeyStore_Mysql::instance();
		$keystore->set('utf8', $string);

		// restore the character set
		$query = "SET NAMES " . $currentCharacterSet;
		$GLOBALS['ISC_CLASS_DB']->Query($query);

		// now retrieve using the original character set
		$result = $keystore->get('utf8');

		$this->assertSame($string, $result);

		// cleanup
		$keystore->delete('utf8');
	}
}
