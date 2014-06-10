<?php

namespace Unit\Logging\Processor;

use Logging\Processor\StoreInfoProcessor;
use Store_Settings;
use Store_Settings_Driver_Dummy;
use Platform\Account;

class StoreInfoProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $settings = new Store_Settings(new Store_Settings_Driver_Dummy(array(
            'HostingId'             => '987654321',
            'StoreHash'             => 'wxyz',
            'PrimaryPleskDomain'    => 'foobar.mybigcommerce.com',
        )));

        $settings->load();

        $account = new Account($settings);

        $processor = new StoreInfoProcessor($account);

        $expected = array(
            'extra' => array(
                'foo'           => 'bar',
                'store_id'      => '987654321',
                'store_hash'    => 'wxyz',
                'domain'        => 'foobar.mybigcommerce.com',
            ),
        );

        $record = $processor(array(
            'extra' => array(
                'foo' => 'bar',
            ),
        ));

        $this->assertEquals($expected, $record);
    }
}
