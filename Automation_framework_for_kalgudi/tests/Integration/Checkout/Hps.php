<?php

use Services\Payments\HPS\Hps;

require_once(dirname(__FILE__) . '/Online.php');

/**
 * @group remote
 */
class Unit_Checkout_Hps extends Unit_Checkout_Online
{
	public $moduleName = 'hps';

	public $vars = array(
		'site_id' => HPS_SITE_ID,
		'device_id' => HPS_DEVICE_ID,
		'license_id' => HPS_LICENSE_ID,
		'user_id' => HPS_USER_ID,
		'password' => HPS_PASSWORD,
		'developer_id' => Hps::DEVELOPER_ID,
		'version' => Hps::VERSION,
		'testmode' => 'YES',
		'hps_transaction_type' => 'AUTH_CAPTURE',
		'require_card_code' => 'YES',
		'avs_check' => 'IssuerApproved',
	);

	public function setUp ()
	{
		parent::setUp();

		$this->form['creditcard_ccno'] = '4444333322221111';
	}

}
