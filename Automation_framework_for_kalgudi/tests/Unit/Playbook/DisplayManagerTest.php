<?php

use PHPUnit_Framework_TestCase as TestCase;
use Playbook\ActivationObserver;
use Playbook\DisplayManager;
use Onboarding\GettingStartedSteps;
use Onboarding\Step;

class DisplayManagerTest extends TestCase
{

    protected function getFeatureMock($available=true, $enabled=true)
    {
        $feature = $this->getMock('Store_Feature');
        $feature
            ->staticExpects($this->any())
            ->method('isAvailable')
            ->with('Runway')
            ->will($this->returnValue($available));

        $feature
            ->staticExpects($this->any())
            ->method('isEnabled')
            ->with('Runway')
            ->will($this->returnValue($enabled));

        return $feature;

    }

    public function testShowModalReturnsFalseWhenDeclinedRecently()
    {

        $this->markTestSkipped('All modals hidden for now');

        $manager = $this
            ->getMockBuilder('Playbook\DisplayManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getDeclineCount', 'getSeenIntro', 'getAcceptedDate', 'getDeclineDate'))
            ->getMock();

        $manager
            ->expects($this->any())
            ->method('getDeclineCount')
            ->will($this->returnValue(DisplayManager::MAX_DECLINES - 1));

        $manager
            ->expects($this->any())
            ->method('getSeenIntro')
            ->will($this->returnValue(false));

        $manager
            ->expects($this->any())
            ->method('getAcceptedDate')
            ->will($this->returnValue(0));

        $manager
            ->expects($this->any())->method('getDeclineDate')
            ->will($this->returnValue(strtotime('-' . (DisplayManager::MAX_DECLINE_UNITS - 1) .' '.DisplayManager::MAX_DECLINE_INTERVALS)));

        $actual = $manager->shouldShowModal(null, $this->getFeatureMock());
        $this->assertFalse($actual);

    }

    public function testShowModalReturnsTrueWhenDeclinedAWhileAgo()
    {

        $this->markTestSkipped('All modals hidden for now');

        $manager = $this
            ->getMockBuilder('Playbook\DisplayManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getDeclineCount', 'getSeenIntro', 'getAcceptedDate', 'getDeclineDate'))
            ->getMock();

        $manager
            ->expects($this->any())
            ->method('getDeclineCount')
            ->will($this->returnValue(DisplayManager::MAX_DECLINES - 1)
        );

        $manager
            ->expects($this->any())
            ->method('getSeenIntro')
            ->will($this->returnValue(false));

        $manager
            ->expects($this->any())
            ->method('getAcceptedDate')
            ->will($this->returnValue(0));

        $manager
            ->expects($this->any())
            ->method('getDeclineDate')
            ->will($this->returnValue(strtotime('-' . (DisplayManager::MAX_DECLINE_UNITS + 5) .' '.DisplayManager::MAX_DECLINE_INTERVALS))
        );

        $actual = $manager->shouldShowModal(null, $this->getFeatureMock());
        $this->assertTrue($actual);

    }

    public function testShowModalReturnsFalseWhenDeclinedMoreThanTwice()
    {

        $this->markTestSkipped('All modals hidden for now');

        $manager = $this
            ->getMockBuilder('Playbook\DisplayManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getDeclineCount', 'getSeenIntro', 'getAcceptedDate', 'getDeclineDate'))
            ->getMock();

        $manager
            ->expects($this->any())
            ->method('getDeclineCount')
            ->will($this->returnValue(DisplayManager::MAX_DECLINES + 1));

        $manager
            ->expects($this->any())
            ->method('getSeenIntro')
            ->will($this->returnValue(false));

        $manager
            ->expects($this->any())
            ->method('getAcceptedDate')
            ->will($this->returnValue(0));

        $manager
            ->expects($this->any())
            ->method('getDeclineDate')
            ->will($this->returnValue(strtotime('-' . (DisplayManager::MAX_DECLINE_UNITS + 1) .' '.DisplayManager::MAX_DECLINE_INTERVALS)));

        $actual = $manager->shouldShowModal(null, $this->getFeatureMock());
        $this->assertFalse($actual);

    }

    public function testShowModalReturnsFalseIfSeenIntro()
    {

        $this->markTestSkipped('All modals hidden for now');

        $manager = $this
            ->getMockBuilder('Playbook\DisplayManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getDeclineCount', 'getSeenIntro', 'getDeclineDate'))
            ->getMock();

        $manager
            ->expects($this->any())
            ->method('getDeclineCount')
            ->will($this->returnValue(DisplayManager::MAX_DECLINES - 1));

        $manager
            ->expects($this->any())
            ->method('getDeclineDate')
            ->will($this->returnValue(strtotime('-' . (DisplayManager::MAX_DECLINE_UNITS + 1) .' '.DisplayManager::MAX_DECLINE_INTERVALS))); // Outside threshold.

        $manager
            ->expects($this->any())
            ->method('getSeenIntro')
            ->will($this->returnValue(true));

        $actual = $manager->shouldShowModal(null, $this->getFeatureMock());
        $this->assertFalse($actual);
    }

    public function testShowModalReturnsFalseIfFeatureNotAvailable()
    {

        $this->markTestSkipped('All modals hidden for now');

        $manager = $this
            ->getMockBuilder('Playbook\DisplayManager')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $actual = $manager->shouldShowModal(null, $this->getFeatureMock(false));
        $this->assertFalse($actual);
    }

    public function testShowModalReturnsFalseIfFeatureAvailableButNotEnabled()
    {

        $this->markTestSkipped('All modals hidden for now');

        $manager = $this
            ->getMockBuilder('Playbook\DisplayManager')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $actual = $manager->shouldShowModal(null, $this->getFeatureMock(true, false));
        $this->assertFalse($actual);
    }
}
