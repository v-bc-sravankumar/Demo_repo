<?php

use Orders\Mappers\ShipmentMapper;

class Unit_Orders_Mappers_ShipmentMapperTest extends \PHPUnit_Framework_TestCase
{
    private $dbRow = array(
        "shipmentid" => 1,
        "shipcustid" => 1,
        "shipvendorid" => 0,
        "shipdate" => 1391993772,
        "shiptrackno" => 12345,
        "shipping_module" => '',
        "shipmethod" => "Fixed Shipping",
        "shiporderid" => 1389060929,
        "shiporderdate" => 1389061256,
        "shipcomments" => "Shipped dude.",
        "shipbillfirstname" => "John",
        "shipbilllastname" => "Citizen",
        "shipbillcompany" => "",
        "shipbillstreet1" => "1 SOMETHING ST",
        "shipbillstreet2" => "",
        "shipbillsuburb" => "SYDNEY",
        "shipbillstate" => "New South Wales",
        "shipbillzip" => "2000",
        "shipbillcountry" => "Australia",
        "shipbillcountrycode" => "AU",
        "shipbillcountryid" => 13,
        "shipbillstateid" => 209,
        "shipbillphone" => "0400000000",
        "shipbillemail" => "john@example.com",
        "shipshipfirstname" => "John",
        "shipshiplastname" => "Citizen",
        "shipshipcompany" => "",
        "shipshipstreet1" => "1 SOMETHING ST",
        "shipshipstreet2" => "",
        "shipshipsuburb" => "SYDNEY",
        "shipshipstate" => "New South Wales",
        "shipshipzip" => "2000",
        "shipshipcountry" => "Australia",
        "shipshipcountrycode" => "AU",
        "shipshipcountryid" => 13,
        "shipshipstateid" => 209,
        "shipshipphone" => "0400000000",
        "shipshipemail" => "john@example.com",
        "order_address_id" => 20
    );
    private $jsonObject = array(
        "id" => 1,
        "customer_id" => 1,
        "vendor_id" => 0,
        "date" => 1391993772,
        "tracking_number" => 12345,
        "shipping_module" => "",
        "method" => "Fixed Shipping",
        "order_id" => 1389060929,
        "order_date" => 1389061256,
        "comments" => "Shipped dude.",
        "order_address_id" => "20",
        "billing_address" => array(
            "firstname" => "John",
            "lastname" => "Citizen",
            "company" => "",
            "street1" => "1 SOMETHING ST",
            "street2" => "",
            "suburb" => "SYDNEY",
            "state" => "New South Wales",
            "zip" => "2000",
            "country" => "Australia",
            "countrycode" => "AU",
            "countryid" => "13",
            "stateid" => "209",
            "phone" => "0400000000",
            "email" => "john@example.com"
        ),
        "shipping_address" => array(
            "firstname" => "John",
            "lastname" => "Citizen",
            "company" => "",
            "street1" => "1 SOMETHING ST",
            "street2" => "",
            "suburb" => "SYDNEY",
            "state" => "New South Wales",
            "zip" => "2000",
            "country" => "Australia",
            "countrycode" => "AU",
            "countryid" => "13",
            "stateid" => "209",
            "phone" => "0400000000",
            "email" => "john@example.com"
        )
    );

    public function testMappingToJSON()
    {
        $mapper = new ShipmentMapper();
        $shipment = new \Orders\Shipment($this->dbRow);
        $json = $mapper->jsonSerialize($shipment);

        // Make sure the fields wound up where we expect.
        $this->assertEquals($this->jsonObject, $json);
    }

    public function testMappingToDB()
    {
        $mapper = new ShipmentMapper();
        $row = $mapper->mapHttpEntityToRecord($this->jsonObject);

        // Make sure the DB fields were populated correctly.
        $this->assertEquals($this->dbRow, $row->toArray());
    }
}