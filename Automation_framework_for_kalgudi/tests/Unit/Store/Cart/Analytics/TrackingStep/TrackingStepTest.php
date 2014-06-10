<?php

namespace Unit\Store\Cart\Analytics\TrackingStep;

use Store\Cart\Analytics\AnalyticsTracker;
use Store\Cart\Analytics\TrackingStep\PreCheckoutTrackingStep;
use Store\Cart\Analytics\TrackingStep\ConfirmationTrackingStep;
use ISC_QUOTE;

class TrackingStepTest extends \PHPUnit_Framework_TestCase
{
    private function getTracker()
    {
        $quote = $this->getMock('\ISC_QUOTE', array('getOrderSource'));
        $quote
            ->expects($this->any())
            ->method('getOrderSource')
            ->will($this->returnValue('www'));

        return new AnalyticsTracker($quote);
    }

    public function testGetStepName()
    {
        $step = new PreCheckoutTrackingStep();
        $this->assertEquals('pre_checkout', $step->getStepName());
    }

    public function testGetNextStep()
    {
        $step = new PreCheckoutTrackingStep();
        $this->assertNull($step->getNextStep());

        $confirmation = new ConfirmationTrackingStep();
        $step->setNextStep($confirmation);

        $this->assertEquals($confirmation, $step->getNextStep());
    }

    public function testGetContext()
    {
        $step = new PreCheckoutTrackingStep();

        $expected = array(
            'has_deleted_product' => false,
            'has_updated_product' => false,
        );

        $this->assertEquals($expected, $step->getContext());
    }

    public function testAddContextData()
    {
        $step = new PreCheckoutTrackingStep();
        $step->addContextData(array(
            'has_deleted_product' => true,
            'foo' => 'bar',
        ));

        $expected = array(
            'has_deleted_product' => true,
            'has_updated_product' => false,
            'foo' => 'bar',
        );

        $this->assertEquals($expected, $step->getContext());
    }

    public function testGetTimeStarted()
    {
        $step = new PreCheckoutTrackingStep();
        $this->assertNull($step->getTimeStarted());

        $step->startTracking();

        $this->assertGreaterThan(0, $step->getTimeStarted());
    }

    public function testGetTimeStopped()
    {
        $step = new PreCheckoutTrackingStep();
        $step->setAnalyticsTracker($this->getTracker());

        $this->assertNull($step->getTimeStopped());

        $step->stopTracking();

        $this->assertGreaterThan(0, $step->getTimeStopped());
    }

    public function testGetTimeStoppedSetsStartTimeIfNotStarted()
    {
        $step = new PreCheckoutTrackingStep();
        $step->setAnalyticsTracker($this->getTracker());

        $step->stopTracking();

        $this->assertGreaterThan(0, $step->getTimeStopped());
        $this->assertEquals($step->getTimeStopped(), $step->getTimeStarted());
    }

    public function testStopTrackingOnLastStepConcludesTracking()
    {
        $tracker = $this->getMock('Store\\Cart\\Analytics\\AnalyticsTracker', array('concludeTracking'), array(new ISC_QUOTE()));
        $tracker->expects($this->once())->method('concludeTracking');

        $step = new PreCheckoutTrackingStep();
        $step->setAnalyticsTracker($tracker);

        $step->stopTracking();
    }

    public function testStopTrackingWithContextData()
    {
        $step = new PreCheckoutTrackingStep();
        $step->setAnalyticsTracker($this->getTracker());

        $step->stopTracking(array(
            'has_deleted_product' => true,
            'foo' => 'bar',
        ));

        $expected = array(
            'has_deleted_product' => true,
            'has_updated_product' => false,
            'foo' => 'bar',
        );

        $this->assertEquals($expected, $step->getContext());
    }

    public function testStopTrackingStartsTrackingOnNextStep()
    {
        $confirmation = new ConfirmationTrackingStep();

        $step = new PreCheckoutTrackingStep();
        $step
            ->setAnalyticsTracker($this->getTracker())
            ->setNextStep($confirmation)
            ->stopTracking();

        $this->assertGreaterThan(0, $confirmation->getTimeStarted());
    }

    public function testReset()
    {
        $step = new PreCheckoutTrackingStep();
        $step->setAnalyticsTracker($this->getTracker());
        $step->stopTracking();
        $step->addContextData(array(
            'has_deleted_product' => true,
            'foo' => 'bar',
        ));

        $step->reset();
        $this->assertNull($step->getTimeStarted());
        $this->assertNull($step->getTimeStopped());
        $this->assertEquals(array(
                'has_deleted_product' => false,
                'has_updated_product' => false,
            ),
            $step->getContext()
        );
    }

    public function testStartTrackingResetsNextStep()
    {
        $confirmation = new ConfirmationTrackingStep();
        $confirmation->setAnalyticsTracker($this->getTracker());
        $confirmation->stopTracking(array('foo' => 'bar'));

        $step = new PreCheckoutTrackingStep();
        $step->setNextStep($confirmation);
        $step->startTracking();

        $this->assertNull($confirmation->getTimeStarted());
        $this->assertNull($confirmation->getTimeStopped());
        $this->assertEquals(array('coupon_entered' => false), $confirmation->getContext());
    }

    public function testGetDuration()
    {
        $step = new PreCheckoutTrackingStep();
        $this->assertEquals(0, $step->getDuration());

        $mock = $this->getMock(
            'Store\\Cart\\Analytics\\TrackingStep\\PreCheckoutTrackingStep',
            array('getTimeStarted', 'getTimeStopped')
        );
        $mock->expects($this->any())->method('getTimeStarted')->will($this->returnValue(200));
        $mock->expects($this->any())->method('getTimeStopped')->will($this->returnValue(500));

        $this->assertEquals(300, $mock->getDuration());
    }
}
