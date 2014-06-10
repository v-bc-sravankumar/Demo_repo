<?php

/**
* ISC-3705
* The engine class is coupled to the template in the constructor.
* Create a mock class here to bypasses the dependency & enable testing.
*/
class MockEngine extends ISC_ADMIN_ENGINE
{
	public function __construct()
	{
	}
}

class Unit_Lib_BigCommerce_VersionNumber extends PHPUnit_Framework_TestCase
{

	public function setUpOnceBeforeClass()
	{
		require_once BUILD_ROOT.'/lib/init.php';
		require_once BUILD_ROOT.'/admin/includes/classes/class.engine.php';
	}

	public function dataProductVersions()
	{
		return array(

			// Superuser
			array('7.3.99', true, '7.3.99'),
			array('7.3.3', true, '7.3.3'),
			array('7.3', true, '7.3'),
			array('7', true, '7'),

			// Regular user
			array('7.3.99', false, ''),
			array('7.3.3', false, ''),
			array('7.3', false, ''),
			array('7', false, ''),

		);
	}

	/**
	 * @dataProvider dataProductVersions
	 * @param string $productVersion
	 * @param bool $isSuperUser
	 * @param string $expected
	 */
	public function testProductCodeFormat($productVersion, $isSuperUser, $expected)
	{
		$engine = new MockEngine;
		$formattedVersion = $engine->formatProductVersionForFooter($productVersion, $isSuperUser);
		$this->assertEquals($expected, $formattedVersion);

	}
}
