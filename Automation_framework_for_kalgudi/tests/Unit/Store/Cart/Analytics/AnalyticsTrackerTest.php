<?php

namespace Unit\Store\Cart\Analytics;

use Store\Cart\Analytics\AnalyticsTracker;
use Store\Cart\Analytics\TrackingStep;
use ISC_QUOTE;
use Store_Config;

class AnalyticsTrackerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStep()
    {
        $tracker = new AnalyticsTracker(new ISC_QUOTE());
        $this->assertInstanceOf('Store\\Cart\\Analytics\\TrackingStep\\ConfirmationTrackingStep', $tracker->getStep('confirmation'));
    }

    public function testGetFirstStep()
    {
        $tracker = new AnalyticsTracker(new ISC_QUOTE());
        $this->assertInstanceOf('Store\\Cart\\Analytics\\TrackingStep\\PreCheckoutTrackingStep', $tracker->getFirstStep());
    }

    public function testSetFirstStep()
    {
        $tracker = new AnalyticsTracker(new ISC_QUOTE());
        $tracker->setFirstStep($tracker->getStep('confirmation'));

        $this->assertInstanceOf('Store\\Cart\\Analytics\\TrackingStep\\ConfirmationTrackingStep', $tracker->getFirstStep());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetUnknownStepThrowsException()
    {
        $tracker = new AnalyticsTracker(new ISC_QUOTE());
        $tracker->getStep('foo');
    }

    public function testGetData()
    {
        $checkoutType = Store_Config::get('CheckoutType');
        Store_Config::override('CheckoutType', 'single');

        $precheckoutStep = $this->getMock(
            'Store\\Cart\\Analytics\\TrackingStep\\PreCheckoutTrackingStep',
            array('getDuration', 'getContext')
        );
        $precheckoutStep->expects($this->any())->method('getDuration')->will($this->returnValue(30));
        $precheckoutStep->expects($this->any())->method('getContext')->will($this->returnValue(array('foo' => 'bar')));

        $confirmationStep = $this->getMock(
            'Store\\Cart\\Analytics\\TrackingStep\\ConfirmationTrackingStep',
            array('getDuration', 'getContext')
        );
        $confirmationStep->expects($this->any())->method('getDuration')->will($this->returnValue(45));
        $confirmationStep->expects($this->any())->method('getContext')->will($this->returnValue(array('hello' => 'world')));

        $quote = $this->getMock('\ISC_QUOTE', array('getOrderSource'));
        $quote
            ->expects($this->any())
            ->method('getOrderSource')
            ->will($this->returnValue('www'));

        $tracker = new AnalyticsTracker($quote, array(
            $precheckoutStep,
            $confirmationStep
        ));

        $data = $tracker->getData();

        $expected = array(
            'steps' => array(
                'pre_checkout' => array(
                    'duration' => 30,
                    'context' => array(
                        'foo' => 'bar',
                    ),
                ),
                'confirmation' => array(
                    'duration' => 45,
                    'context' => array(
                        'hello' => 'world',
                    ),
                ),
            ),
            'order_source' => 'www',
            'checkout_type' => 'single',
            'total_duration' => 75,
            'experiments' => \Config\Experiment::getSelectedExperiments(),
        );

        $this->assertEquals($expected, $data);

        Store_Config::override('CheckoutType', $checkoutType);
    }

    public function testGetDataForSinglepageCheckout()
    {
        $quote = $this->getMock('\ISC_QUOTE', array('getOrderSource'));
        $quote
            ->expects($this->any())
            ->method('getOrderSource')
            ->will($this->returnValue('www'));

        $checkoutType = Store_Config::get('CheckoutType');
        Store_Config::override('CheckoutType', 'single');

        $tracker = new AnalyticsTracker($quote, array());
        $data = $tracker->getData();

        $expected = array(
            'steps' => array(),
            'order_source' => 'www',
            'checkout_type' => 'single',
            'total_duration' => 0,
            'experiments' => \Config\Experiment::getSelectedExperiments(),
        );

        $this->assertEquals($expected, $data);

        Store_Config::override('CheckoutType', $checkoutType);
    }

    public function testGetDataForMultipageCheckout()
    {
        $quote = $this->getMock('\ISC_QUOTE', array('getOrderSource'));
        $quote
            ->expects($this->any())
            ->method('getOrderSource')
            ->will($this->returnValue('www'));

        $checkoutType = Store_Config::get('CheckoutType');
        Store_Config::override('CheckoutType', 'multipage');

        $tracker = new AnalyticsTracker($quote, array());
        $data = $tracker->getData();

        $expected = array(
            'steps' => array(),
            'order_source' => 'www',
            'checkout_type' => 'multipage',
            'total_duration' => 0,
            'experiments' => \Config\Experiment::getSelectedExperiments(),
        );

        $this->assertEquals($expected, $data);

        Store_Config::override('CheckoutType', $checkoutType);
    }

    /**
     * If we're split shipping, then we should be on multipage checkout,
     * regardless of the CheckoutType configuration.
     */
    public function testCheckoutTypeIsMultipageForSplitShipping()
    {
        $quote = $this->getMock('\ISC_QUOTE', array('getOrderSource'));
        $quote
            ->expects($this->any())
            ->method('getOrderSource')
            ->will($this->returnValue('www'));

        $quote->setIsSplitShipping(true);

        $checkoutType = Store_Config::get('CheckoutType');
        Store_Config::override('CheckoutType', 'single');

        $tracker = new AnalyticsTracker($quote, array());
        $data = $tracker->getData();

        $expected = array(
            'steps' => array(),
            'order_source' => 'www',
            'checkout_type' => 'multipage',
            'total_duration' => 0,
            'experiments' => \Config\Experiment::getSelectedExperiments(),
        );

        $this->assertEquals($expected, $data);

        Store_Config::override('CheckoutType', $checkoutType);
    }

    public function testGetQuote()
    {
        $quote = new ISC_QUOTE();
        $tracker = new AnalyticsTracker($quote);
        $this->assertEquals($quote, $tracker->getQuote());
    }

    public function testSetQuote()
    {
        $tracker = new AnalyticsTracker(new ISC_QUOTE());
        $quote = new ISC_QUOTE();
        $tracker->setQuote($quote);
        $this->assertEquals($quote, $tracker->getQuote());
    }

    public function testQuoteIsNotSerialized()
    {
        $tracker = new AnalyticsTracker(new ISC_QUOTE());
        $serializedTracker = serialize($tracker);
        $tracker = unserialize($serializedTracker);

        $this->assertNull($tracker->getQuote());
    }
}