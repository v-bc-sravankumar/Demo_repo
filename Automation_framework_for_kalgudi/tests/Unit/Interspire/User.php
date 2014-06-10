<?php

class Unit_Interspire_User extends PHPUnit_Framework_TestCase
{
	private $userManager;

	public function setUp()
	{
		$this->userManager = new TestAdminUser();
	}

	public function enableModernUIDataProvider() {
		return array(
			array(true, true, 0, 1),
			array(true, false, 0, 0),
			array(false, true, 1, 1),
			array(false, false, 1, 0)
		);
	}

	/**
	 * @dataProvider enableModernUIDataProvider
	 */
	public function testEnableModernUI($firstTimeExists, $enableMUI, $expectedFirstTime, $expectedMUI)
	{
		$userId = 1;
		$ks = $this->getMock('Interspire_KeyStore', array('set', 'exists'), array(), "", false);

		$ks	->expects($this->at(0))
			->method('exists')
			->with($this->equalTo(MODERN_UI_ENABLED_FIRST_TIME . $userId))
			->will($this->returnValue($firstTimeExists));

		$ks	->expects($this->at(1))
			->method('set')
			->with($this->equalTo(MODERN_UI_ENABLED . $userId, $expectedMUI));

		$ks	->expects($this->at(2))
			->method('set')
			->with($this->equalTo(MODERN_UI_ENABLED_FIRST_TIME . $userId, $expectedFirstTime));

		$this->userManager->enableModernUI($userId, $enableMUI, $ks);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testEnableModernUiWithInvalidUser() {
		$this->userManager->enableModernUI("invalid user id");
	}
}

class TestAdminUser extends ISC_ADMIN_USER
{
	public function __construct() { }
}

