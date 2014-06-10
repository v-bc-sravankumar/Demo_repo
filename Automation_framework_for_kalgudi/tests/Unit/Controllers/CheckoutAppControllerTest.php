<?php
namespace Unit\Controllers;

use Store\Controllers;

class CheckoutAppControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSettingsContainsTheCorrectKeys()
    {

        $config = new \Store_Config();
        $config::override('GuestCheckoutEnabled', 'foo');
        $config::override('EnableOrderComments', 'bar');
        $config::override('EnableOrderTermsAndConditions', 'baz');
        $config::override('ShowMailingListInvite', 'beep');
        $config::override('MailAutomaticallyTickNewsletterBox', 'bop');

        $controller = $this->getMock('\Storefront\CheckoutAppController', array('getConfig'));
        $controller->expects($this->once())->method('getConfig')->will($this->returnValue($config));
        $actual = $controller->injectAction();

        $this->assertEquals('foo', $actual['settings']['GuestCheckoutEnabled']);
        $this->assertEquals('bar', $actual['settings']['EnableOrderComments']);
        $this->assertEquals('baz', $actual['settings']['EnableOrderTermsAndConditions']);
        $this->assertEquals('beep', $actual['settings']['ShowMailingListInvite']);
        $this->assertEquals('bop', $actual['settings']['MailAutomaticallyTickNewsletterBox']);

    }

}