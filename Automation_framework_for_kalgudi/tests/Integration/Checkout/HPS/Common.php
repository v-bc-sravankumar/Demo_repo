<?php
use Services\Payments\HPS\Hps;

abstract class Unit_Checkout_HPS_Common extends PHPUnit_Framework_TestCase
{
	/**
	 * Load the internal BC HPS Account credentials
	 */
	public function loadTestCredentials(&$gateway)
	{
		$gateway->setSiteId(HPS_SITE_ID);
		$gateway->setDeviceId(HPS_DEVICE_ID);
		$gateway->setLicenseId(HPS_LICENSE_ID);
		$gateway->setUserId(HPS_USER_ID);
		$gateway->setPassword(HPS_PASSWORD);
		$gateway->setDeveloperId(Hps::DEVELOPER_ID);
		$gateway->setVersion(Hps::VERSION);
		$gateway->setTestMode(true);
	}

}
