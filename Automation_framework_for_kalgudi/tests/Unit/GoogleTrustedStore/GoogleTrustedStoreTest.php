<?php

use Feature\GoogleTrustedStores\GoogleTrustedStore;
use Repository\OrderCancellations;
use Store_Feature as Feature;

class GoogleTrustedStoreTest extends \PHPUnit_Framework_TestCase
{

    const FEATURE_GOOGLETRUSTEDSTORES = 'GoogleTrustedStores';

    public function setUp()
    {
        $this->controller = new GoogleTrustedStoreController();
    }

    public function tearDown()
    {
        Store_Config::override('Feature_GoogleTrustedStores', false);
    }

    public function testValidateShippingFeedHeaders()
    {
        $gts = $this
            ->getMockBuilder('Feature\GoogleTrustedStores\GoogleTrustedStore')
            ->setMethods(array('retrieveShipments', 'getModuleVars'))
            ->getMock();

        $gts
            ->expects($this->any())
            ->method('getModuleVars')
            ->will($this->returnValue(array()));

        $gts
            ->expects($this->any())
            ->method('retrieveShipments')
            ->will($this->returnValue(array(99, 100, 'shipping_ups', 1382408125)));

        $result = $gts->generateShippingFeed();
        //check headers.
        $cols = array('merchant order id', 'tracking number', 'carrier code', 'other carrier name', 'ship date'); //$gts->shippingColumnNames
        $header = implode($gts::DELIMITER, $cols);
        $this->assertContains($header, $result);
    }

    public function testShippingFeedContent()
    {
        $gts = new GoogleTrustedStore();

        $gtsMock = $this->getMock('Feature\GoogleTrustedStores\GoogleTrustedStore', array('retrieveShipments', 'processShipmentRows'));

        $gtsMock
        ->expects($this->any())
        ->method('retrieveShipments')
        ->will($this->returnValue(array(array(
            "shipdate" => 1382919000,
            "shipping_module" => "shipping_fedex",
            "shiporderid" => 100,
            "shiptrackno" => 1234,
            ))));

        $mockData = $gtsMock->retrieveShipments();

        $processed = $gts->processShipmentRows($mockData);

        $actual = array(array(100, 1234, "FedEx", "", "2013-10-28"));

        $this->assertEquals($processed, $actual);
    }

    public function testFeatureFlag()
    {
        Feature::override(self::FEATURE_GOOGLETRUSTEDSTORES, true);
        $this->assertEquals(true, Feature::isEnabled('GoogleTrustedStores'));

        Feature::override(self::FEATURE_GOOGLETRUSTEDSTORES, false);
        $this->assertEquals(false, Feature::isEnabled('GoogleTrustedStores'));
    }

    /**
     * @dataProvider getShippingModuleTestData
     */
    public function testShippingModule($value, $expected)
    {
        $gts = $this->getMock('Feature\GoogleTrustedStores\GoogleTrustedStore', array('getModuleVars'));
        
        $gts
            ->expects($this->any())
            ->method('getModuleVars')
            ->will($this->returnValue(array()));

        $actual = $gts->resolveShippingModule($value);

        $this->assertEquals($expected, $actual);
    }

    public function getShippingModuleTestData()
    {
        return array(
                array('shipping_ups', array(
                        'carrier_code' => 'UPS',
                        'other_carrier_name' => ''),
                        ),
                array('shipping_upsonline', array(
                        'carrier_code' => 'UPS',
                        'other_carrier_name' => ''),
                        ),
                array('shipping_fedex', array(
                        'carrier_code' => 'FedEx',
                        'other_carrier_name' => ''),
                        ),
                array('shipping_usps', array(
                        'carrier_code' => 'USPS',
                        'other_carrier_name' => ''),
                        ),
                array('nonstandard', array(
                        'carrier_code' => 'Other',
                        'other_carrier_name' => 'OTHER'),
                        ),
                );
    }

    public function testValidateCancellationFeedHeaders()
    {
        $gts = $this->getMock('Feature\GoogleTrustedStores\GoogleTrustedStore', array('getCancellationRows'));

        $gts
        ->expects($this->any())
        ->method('getCancellationRows')
        ->will($this->returnValue(array(5, 'FraudFake')));

        $cancellationRows = $gts->getCancellationRows();
        $cancellationRows = array($cancellationRows);
        $cols = array('merchant order id', 'reason');

        $result = $gts->generateCancellationFeed($cancellationRows);
        $header = implode($gts::DELIMITER, $cols);
        $this->assertContains($header, $result);

    }

    /**
     * @dataProvider testValidReasonsData
     */

    public function testValidReasons($value, $expected)
    {
        $actual = OrderCancellations::checkReason($value);
        $this->assertEquals($expected, $actual);
    }

    public function testValidReasonsData()
    {
        return array(
            array('BuyerCanceled', true),
            array('DuplicateInvalid', true),
            array('MerchantCanceled', true),
            array('FraudFake', true),
            array('Xfactorisagreatshow', false),
        );
    }

    public function testHashGeneration()
    {
        $gts = new GoogleTrustedStore();
        $hash = $gts->generateHash();
        $this->assertRegExp('/^[0-9a-f]{32}$/i', $hash);
    }
}
