<?php

namespace Unit\Application\Model\Platform;

use Platform\Account;
use Store_Settings;
use Store_Settings_Driver_Dummy;
use Store_Feature;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    private function resetBillingSystemVars()
    {
        $vars = array(
            'global' => isset($_ENV['BILLING_SYSTEM_URL']) ? $_ENV['BILLING_SYSTEM_URL'] : '',
            'getenv' => getenv('BILLING_SYSTEM_URL') ?: '',
        );

        putenv('BILLING_SYSTEM_URL=');
        unset($_ENV['BILLING_SYSTEM_URL']);

        return $vars;
    }

    private function restoreBillingSystemVars($vars)
    {
        putenv('BILLING_SYSTEM_URL=' . $vars['getenv']);

        if ($vars['global']) {
            $_ENV['BILLING_SYSTEM_URL'] = $vars['global'];
        }
        else {
            unset($_ENV['BILLING_SYSTEM_URL']);
        }
    }

    public function testGetBillingSystemUrlWithGlobalEnv()
    {
        $backupVars = $this->resetBillingSystemVars();

        // $_ENV should prioritise over getenv
        $_ENV['BILLING_SYSTEM_URL'] = 'http://globalenv.com';
        putenv('BILLING_SYSTEM_URL=http://getenv.com');

        $this->assertEquals('http://globalenv.com', Account::getInstance()->getBillingSystemUrl());

        $this->restoreBillingSystemVars($backupVars);
    }

    public function testGetBillingSystemUrlWithGetenv()
    {
        $backupVars = $this->resetBillingSystemVars();

        putenv('BILLING_SYSTEM_URL=http://getenv.com');

        $this->assertEquals('http://getenv.com', Account::getInstance()->getBillingSystemUrl());

        $this->restoreBillingSystemVars($backupVars);
    }

    public function testGetBillingSystemUrlWithConfigEnv()
    {
        $backupVars = $this->resetBillingSystemVars();

        $this->assertEquals('https://account-bigcommerce.interspire', Account::getInstance()->getBillingSystemUrl());

        $this->restoreBillingSystemVars($backupVars);
    }

    public function testGetUpgradeLinkForBigcommerceStore()
    {
        $franchised = Store_Feature::isEnabled('Franchised');
        Store_Feature::override('Franchised', false);

        $settings = new Store_Settings(new Store_Settings_Driver_Dummy(array(
            'HostingId' => 1234,
        )));
        $settings->load();

        $account = new Account($settings);
        $this->assertEquals('https://account-bigcommerce.interspire/upgradeplan.php?id=1234&type=package', $account->getUpgradeLink());

        Store_Feature::override('Franchised', $franchised);
    }

    public function testGetUpgradeLinkForFranchisedStore()
    {
        $franchised = Store_Feature::isEnabled('Franchised');
        Store_Feature::override('Franchised', true);

        $settings = new Store_Settings(new Store_Settings_Driver_Dummy(array(
            'HostingId' => 1234,
        )));
        $settings->load();

        $account = new Account($settings);
        $this->assertEquals('https://account-bigcommerce.interspire/upgrade.php?id=1234&type=package', $account->getUpgradeLink());

        Store_Feature::override('Franchised', $franchised);
    }

    public function testGetAccountLinkForEarlyBillingAdopter()
    {

        $settings = new Store_Settings(new Store_Settings_Driver_Dummy(array(
            'HostingId' => 1234,
            'Feature_NewBillingSystem' => true,
        )));
        $settings->load();

        $account = new Account($settings);
        $this->assertEquals('https://manage-dev.bigcommerce.net', $account->getBillingSystemUrl());

    }

    public function testGetAccountLinkForLateBillingAdopter()
    {

        $settings = new Store_Settings(new Store_Settings_Driver_Dummy(array(
            'HostingId' => 1234,
            'Feature_NewBillingSystem' => false,
        )));
        $settings->load();

        $account = new Account($settings);
        $this->assertEquals('https://account-bigcommerce.interspire', $account->getBillingSystemUrl());

    }

}
