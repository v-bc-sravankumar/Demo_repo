<?php

use PHPUnit\TestCases\TransactionalIntegrationTestCase;

/**
 * @transactional
 */
class Integration_Store_Api_Version_2_Resource_Optionsets_Options extends TransactionalIntegrationTestCase {

	const IPOD_SHUFFLE_OPTION_SET = 7;
	const IPOD_SHUFFLE_COLOR_OPTION = 16;
	
	const PIXELSKIN_OPTION_SET = 4;
	const PIXELSKIN_COLOR_OPTION = 10;

	const SILVER = 7;
	const BLACK = 8;
	const PURPLE = 9;
	const BLUE = 10;
	const GREEN = 11;
	const YELLOW = 12;
	const ORANGE = 13;
	const PINK = 14;
	const RED = 39;

	/**
	 * @var Store_Api_Version_2_Resource_Optionsets_Options
	 */
	protected static $resource;

	public static function setUpBeforeClass()
	{
		self::$resource = new Store_Api_Version_2_Resource_Optionsets_Options();
	}

	protected function getOptionSetOptions($optionSet, $option) {
		$req = new Interspire_Request(null, null, null, array('CONTENT_TYPE' => 'application/json'));
		$req->setUserParam('optionsets', $optionSet);
		$req->setUserParam('options', $option);
		return self::$resource->getAction($req)->getData(true);
	}

	public function testGetOptionValuesWhenOptionSetHasSpecifiedValues() {
		$optionSetOption = $this->getOptionSetOptions(self::IPOD_SHUFFLE_OPTION_SET, self::IPOD_SHUFFLE_COLOR_OPTION);
		$optionSetOptionValues = $optionSetOption['values'];
		$optionValueIds = array_map(function ($ov) {
			return $ov['option_value_id'];
		}, $optionSetOptionValues);
		sort($optionValueIds);
		$this->assertEquals(array(
			self::SILVER,
			self::BLUE,
			self::GREEN,
			self::PINK,
			self::RED,
		), $optionValueIds);
	}

	public function testGetAllOptionValuesWhenOptionSetHasNoExplicitValues() {
		$optionSetOption = $this->getOptionSetOptions(self::PIXELSKIN_OPTION_SET, self::PIXELSKIN_COLOR_OPTION);
		$optionSetOptionValues = $optionSetOption['values'];
		$optionValueIds = array_map(function ($ov) {
			return $ov['option_value_id'];
		}, $optionSetOptionValues);
		sort($optionValueIds);
		$this->assertEquals(array(
			self::SILVER,
			self::BLACK,
			self::PURPLE,
			self::BLUE,
			self::GREEN,
			self::YELLOW,
			self::ORANGE,
			self::PINK,
			self::RED,
		), $optionValueIds);
	}
}