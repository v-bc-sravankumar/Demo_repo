<?php

namespace Unit\Store;

// required for auth constants
require_once ISC_BASE_PATH . '/admin/includes/classes/class.auth.php';

class NoticesTest extends \PHPUnit_Framework_TestCase
{
    public function lastFtpDataProvider()
    {
        /**
         * There are three variables:
         * - Last FTP access (listed below)
         * - Current time (listed below)
         * - Expected notice (which notice is expected to be shown)
         *
         * We need to test every combination of these.
         *
         * TIMES TO TEST
         *
         * Just for FTP access:
         * - Never
         * - Not within the last 180 days
         * - Between 180 days and 31 days
         *
         * Just for "current" time:
         * - Outside the notice period
         *
         * For both:
         * - Between 31 days and 21 days
         * - Between 21 days and 14 days
         * - Between 14 days and 7 days
         * - Between 7 days and 3 days
         * - In the last 3 days
         */

        $timeOutside     = strtotime('2013-06-01');
        $never           = 0;
        $before180       = strtotime('2012-01-01');
        $between180And21 = strtotime('2013-07-01');
        $between21And14  = strtotime('2013-08-15');
        $between14And7   = strtotime('2013-08-22');
        $between7And3    = strtotime('2013-08-26');
        $after3          = strtotime('2013-08-29');

        // `null` means no notice is expected to be shown
        return array(
            array($never, $timeOutside,    null),
            array($never, $between21And14, null),
            array($never, $between14And7,  null),
            array($never, $between7And3,   null),
            array($never, $after3,         null),

            array($before180, $timeOutside,    null),
            array($before180, $between21And14, null),
            array($before180, $between14And7,  null),
            array($before180, $between7And3,   null),
            array($before180, $after3,         null),

            array($between180And21, $timeOutside,    null),
            array($between180And21, $between21And14, 21),
            array($between180And21, $between14And7,  null),
            array($between180And21, $between7And3,   null),
            array($between180And21, $after3,         null),

            array($between21And14, $timeOutside,    null),
            array($between21And14, $between21And14, 21),
            array($between21And14, $between14And7,  14),
            array($between21And14, $between7And3,   null),
            array($between21And14, $after3,         null),

            array($between14And7, $timeOutside,    null),
            array($between14And7, $between21And14, 21),
            array($between14And7, $between14And7,  14),
            array($between14And7, $between7And3,   7),
            array($between14And7, $after3,         null),

            array($between7And3, $timeOutside,    null),
            array($between7And3, $between21And14, 21),
            array($between7And3, $between14And7,  14),
            array($between7And3, $between7And3,   7),
            array($between7And3, $after3,         3),
        );
    }

    /**
     * @dataProvider lastFtpDataProvider
     */
    public function testSetFtpWarnings($lastFtp, $currentTime, $expectedNotice)
    {
        $db = $this->getMockBuilder('\Db_Mysql')
                   ->setMethods(array('Query', 'fetch'))
                   ->getMock();

        $db->expects($this->at(0))
           ->method('Query')
           ->with($this->equalTo(
               'SELECT MAX(dateline) AS time '.
               'FROM [|PREFIX|]bigcommerce_usage '.
               'WHERE trafficusage_ftpin>0 OR trafficusage_ftpout>0'
           ));
        $db->expects($this->at(1))
           ->method('fetch')
           ->will($this->returnValue(array('time' => $lastFtp)));

        $notices = $this->getMockBuilder('\Store\Notices')
                        ->setMethods(array('add'))
                        ->disableOriginalConstructor()
                        ->getMock();

        if ($expectedNotice === null) {
            $notices->expects($this->exactly(0))
                    ->method('add');
        } else {
            $notices->expects($this->at(0))
                    ->method('add');
        }

        $validator = new TestValidator();
        $keyStore  = new TestKeyStore();

        $notices->setFtpWarning($db, $validator, $keyStore, $currentTime);
    }
}

class TestValidator
{
    public function HasPermission($permission)
    {
        return true;
    }

    public function GetUser()
    {
        return array(
            'pk_userid' => 0,
        );
    }
}

class TestKeyStore
{
    public function exists($key)
    {
        return false;
    }

    public function set($key, $value)
    {
    }
}

