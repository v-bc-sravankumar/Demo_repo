<?php

namespace Unit\Platform\Notices;

use Platform\Notices\DiskUsageNotice;

class DiskUsageNoticeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $isCloseToStorageLimit
     * @param $isOverStorageLimit
     * @param $expected
     * @dataProvider provideIsDisplayableTestData
     */
    public function testIsDisplayableWhenCloseToStorageLimit($isCloseToStorageLimit, $isOverStorageLimit, $expected)
    {
        $notice = new DiskUsageNotice();

        $mockAc = $this->getMock('BigCommerce_Account',
            array('isCloseToStorageLimit', 'isOverStorageLimit'),
            array(),
            'Mock_BigCommerce_Account',
            false);

        $mockAc->expects($this->any())
            ->method('isCloseToStorageLimit')
            ->will($this->returnValue($isCloseToStorageLimit));

        $mockAc->expects($this->any())
            ->method('isOverStorageLimit')
            ->will($this->returnValue($isOverStorageLimit));

        $notice->setAccount($mockAc);

        $this->assertEquals($notice->isDisplayable(), $expected);
    }

    public function provideIsDisplayableTestData()
    {
        return array(
            array(true, true, true),
            array(true, false, true),
            array(false, true, true),
            array(false, false, false),
        );
    }
}