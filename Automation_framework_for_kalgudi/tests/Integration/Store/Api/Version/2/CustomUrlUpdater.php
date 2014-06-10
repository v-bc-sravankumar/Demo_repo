<?php

class Unit_Lib_Store_Api_Version_2_CustomUrlUpdater extends Interspire_IntegrationTest
{
	public function setUp()
	{
		ParseLangFile(ISC_BASE_PATH . '/language/' . Store_Config::get('Language') . '/api.ini');
	}

	public function testInvalidUrlFails()
	{
		$url = '/../invalid';

		try {
			$updater = new Store_Api_Version_2_CustomUrlUpdater();
			$updater->validateAndSaveCustomUrl(0, $url, Store_CustomUrl::TARGET_TYPE_PRODUCT);
		}
		catch (Store_Api_Exception_Request_InvalidField $exception) {
			$expectedMessage = GetLang('Store_Api_InputValidator_CustomUrl_Invalid', array('value' => $url));
			$this->assertEquals($expectedMessage, $exception->getDetail('invalid_reason'));
			return;
		}

		$this->fail('Expected exception Store_Api_Exception_Request_InvalidField has not been raised.');
	}

	public function testUrlGenerationFailureFails()
	{
		Store_Config::override('CustomUrlProductFormat', 'seo_long');

		try {
			$updater = new Store_Api_Version_2_CustomUrlUpdater();
			// id of 0 should result in url generation failure
			$updater->validateAndSaveCustomUrl(0, null, Store_CustomUrl::TARGET_TYPE_PRODUCT);
		}
		catch (Store_Api_Exception_Request_InvalidField $exception) {
			$this->assertEquals(GetLang('Store_Api_InputValidator_CustomUrl_GenerateFailed'), $exception->getDetail('invalid_reason'));
			return;
		}

		$this->fail('Expected exception Store_Api_Exception_Request_InvalidField has not been raised.');
	}

	public function testDuplicateUrlFails()
	{
		$url = '/foobar-999.html';

		$customUrl = new Store_CustomUrl();
		$customUrl
			->setTargetType(Store_CustomUrl::TARGET_TYPE_PRODUCT)
			->setTargetId(999)
			->setUrl($url);

		if (!$customUrl->save()) {
			$this->fail('Failed to save custom url.');
		}

		try {
			$updater = new Store_Api_Version_2_CustomUrlUpdater();
			$updater->validateAndSaveCustomUrl(1000, $url, Store_CustomUrl::TARGET_TYPE_PRODUCT);
		}
		catch (Store_Api_Exception_Request_InvalidField $exception) {
			$expectedMessage = GetLang('Store_Api_InputValidator_CustomUrl_Duplicate', array('value' => $url));
			$this->assertEquals($expectedMessage, $exception->getDetail('invalid_reason'));

			$customUrl->delete();
			return;
		}

		$customUrl->delete();

		$this->fail('Expected exception Store_Api_Exception_Request_InvalidField has not been raised.');
	}

	public function testGenerateUrlSucceeds()
	{
		$updater = new Store_Api_Version_2_CustomUrlUpdater();
		$updater->validateAndSaveCustomUrl(998, null, Store_CustomUrl::TARGET_TYPE_PRODUCT);

		/**
		 * @var $customUrl Store_CustomUrl
		 */
		$customUrl = Store_CustomUrl::findByContent(Store_CustomUrl::TARGET_TYPE_PRODUCT, 998)->first();

		$this->assertInstanceOf('Store_CustomUrl', $customUrl);
		$this->assertEquals(Store_CustomUrl::TARGET_TYPE_PRODUCT, $customUrl->getTargetType());
		$this->assertEquals(998, $customUrl->getTargetId());
		$this->assertNotEmpty($customUrl->getUrl());

		$customUrl->delete();
	}

	public function testValidUrlSaves()
	{
		$url = '/my-custom-url-997.html';

		$updater = new Store_Api_Version_2_CustomUrlUpdater();
		$savedUrl = $updater->validateAndSaveCustomUrl(997, $url, Store_CustomUrl::TARGET_TYPE_PRODUCT);

		$this->assertEquals($savedUrl, $url);

		/**
		 * @var $customUrl Store_CustomUrl
		 */
		$customUrl = Store_CustomUrl::findByContent(Store_CustomUrl::TARGET_TYPE_PRODUCT, 997)->first();

		$this->assertInstanceOf('Store_CustomUrl', $customUrl);
		$this->assertEquals(Store_CustomUrl::TARGET_TYPE_PRODUCT, $customUrl->getTargetType());
		$this->assertEquals(997, $customUrl->getTargetId());
		$this->assertEquals($url, $customUrl->getUrl());

		$customUrl->delete();
	}
}
