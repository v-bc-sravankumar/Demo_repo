<?php

namespace Unit\App\Model\Platform;

use \PHPUnit_Framework_TestCase;
use \Platform\Partner;

class PartnerTest extends PHPUnit_Framework_TestCase
{
    protected $partner;

    public function setUp()
    {
        // @TODO: the fixture could be simpler and should be extracted
        $configData = array(
            'HostingId' => 1234,
            'Feature_Franchised' => true,
            'Partner' => array(
                'Code' => 'EIG',
                'PaymentProviders' => '',
                'SupportContacts' => array(
                    'phone' => '1-866-602-4291',
                    'availability' => array(
                        'Mon-Fri' => '6AM to 5PM (PST)',
                    ),
                    'urls' => array(
                        'forum' => 'http://community.homestead.com/',
                    ),
                ),
                'LiveChat' => false,
                'OnlineSupport' => false,
                'MyAccount' => array(
                    'InvoicesAndBilling' => false,
                    'UpgradeAccount' => false,
                    'PurchaseHistory' => false,
                    'AccountSummary' => true,
                    'CommunityForum' => true,
                    'AccountDetails' => true,
                ),
                'Tools' => array(
                    'EmailAccounts' => false,
                    'SSLCertificate' => false,
                ),
                'Marketing' => array(
                    'AbandonedCartNotifications' => false,
                ),
            ),
        );
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($configData));
        $settings->load();


        $this->partner = new \Platform\Partner($settings);
    }

    public function tearDown()
    {
        $this->partner = null;
    }

    public function testIsAvailableToPartner()
    {
        $this->assertFalse(\Platform\Partner::isAvailableToPartner('abc', 'def'), 'did not find dummy config');
        $this->assertFalse(\Platform\Partner::isAvailableToPartner('Marketing', 'AbandonedCartNotifications'), 'AbandonedCartNotifications should be false');
    }
}
