<?php

class Unit_Core_Mobile extends Interspire_IntegrationTest
{
	/**
	 * @dataProvider mobileDeviceDataProvider
	 */
	public function testMobileDeviceIdentification($useragent, $expectedIdentity)
	{
		$identifiedDevice = Store_Mobile::getDeviceTypeFromUserAgent($useragent);
		$this->assertEquals($expectedIdentity, $identifiedDevice);
	}

	public function mobileDeviceDataProvider()
	{
		return array(
			// iPhone running 3.1.3 firmware
			array(
				'Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_1_3 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7E18 Safari/528.16',
				array('category' => 'phone', 'device' => 'iphone'),
			),
			// iPod running 3.0 firmware
			array(
				'Mozilla/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A101a Safari/419.3',
				array('category' => 'phone', 'device' => 'ipod'),
			),
			// iPad running 3.2 firmware
			array(
				'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) version/4.0.4 Mobile/7B367 Safari/531.21.10',
				array('category' => 'tablet', 'device' => 'ipad'),
			),
			// Google Nexus One
			array(
				'Mozilla/5.0 (Linux; U; Android 2.1; en-us; Nexus One Build/ERD62) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17',
				array('category' => 'phone', 'device' => 'android'),
			),
			// HTC Desire
			array(
				'Mozilla/5.0 (Linux; U; Android 2.1-update1; en-us; HTC Desire Build/ERE27) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17',
				array('category' => 'phone', 'device' => 'android'),
			),
			// Plam Pre
			array(
				'Mozilla/5.0 (webOS/1.0; U; en-US) AppleWebKit/525.27.1 (KHTML, like Gecko) Version/1.0 Safari/525.27.1 Pre/1.0',
				array('category' => 'phone', 'device' => 'pre'),
			),
			// Safari (Desktop)
			array(
				'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-us) AppleWebKit/534.1+ (KHTML, like Gecko) Version/5.0 Safari/533.16',
				false
			),
			// Google Chrome (Desktop)
			array(
				'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.2 (KHTML, like Gecko) Chrome/6.0.451.0 Safari/534.2',
				false
			),
		);
	}
}