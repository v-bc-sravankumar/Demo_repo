<?php

namespace Unit\Platform\Notices;

use Platform\Notices\BillingNotice;

class BillingNoticeTest extends \PHPUnit_Framework_TestCase
{
    public function testIsDisplayableWhenUnpaidInvoiceIdExists()
    {
        \Store_Config::override('UnpaidInvoiceId', 9999);
        \Store_Config::override('Feature_UnpaidInvoiceWarning', true);

        $notice = new BillingNotice();

        $this->assertTrue($notice->isDisplayable());

        \Store_Config::override('UnpaidInvoiceId', \Store_Config::getOriginal('UnpaidInvoiceId'));
        \Store_Config::override('Feature_UnpaidInvoiceWarning', \Store_Config::getOriginal('Feature_UnpaidInvoiceWarning'));
    }

    public function testIsNotDisplayableWhenNoUnpaidInvoiceIdExists()
    {
        \Store_Config::override('UnpaidInvoiceId', null);
        \Store_Config::override('Feature_UnpaidInvoiceWarning', true);

        $notice = new BillingNotice();

        $this->assertFalse($notice->isDisplayable());

        \Store_Config::override('UnpaidInvoiceId', \Store_Config::getOriginal('UnpaidInvoiceId'));
        \Store_Config::override('Feature_UnpaidInvoiceWarning', \Store_Config::getOriginal('Feature_UnpaidInvoiceWarning'));
    }

    public function testIsNotDisplayableWhenFeatureFlagIsOff()
    {
        \Store_Config::override('UnpaidInvoiceId', 9999);
        \Store_Config::override('Feature_UnpaidInvoiceWarning', false);

        $notice = new BillingNotice();

        $this->assertFalse($notice->isDisplayable());

        \Store_Config::override('UnpaidInvoiceId', \Store_Config::getOriginal('UnpaidInvoiceId'));
        \Store_Config::override('Feature_UnpaidInvoiceWarning', \Store_Config::getOriginal('Feature_UnpaidInvoiceWarning'));
    }

}