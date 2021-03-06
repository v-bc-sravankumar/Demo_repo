<?php
namespace Unit\Payments;

class SecureNetTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['ShopPath'] = 'abc';
        $GLOBALS['ShopPathSSL'] = 'abc';
        $GLOBALS['ISC_CLASS_LOG'] = new \Debug\LegacyLogger();
    }

    public function tearDown()
    {
        unset($GLOBALS['ShopPath']);
        unset($GLOBALS['ShopPathSSL']);
        unset($GLOBALS['ISC_CLASS_LOG']);
    }

    public function testMapToMyPaymentSchema()
    {
        $card = new \CHECKOUT_SECURENET();

        $data = new \stdClass();
        $data->cardtype = 'VISA';
        $data->name = 'John Doe';
        $data->number = 4111111111111111;
        $data->month = 12;
        $data->year = 2012;
        $data->ccv_check = 123;

        $this->assertEquals(array(
            'creditcard_cctype' => 'VISA',
            'creditcard_name' => 'John Doe',
            'creditcard_ccno' => 4111111111111111,
            'creditcard_ccexpm' => 12,
            'creditcard_ccexpy' => 12,
            'creditcard_cccvd' => 123,
        ), $card->mapToMyPaymentSchema($data));
    }

    public function testMapToMyPaymentSchema_missingData()
    {
        $card = new \CHECKOUT_SECURENET();

        $data = new \stdClass();
        $data->cardtype = 'VISA';
        $data->name = 'John Doe';
        $data->number = 4111111111111111;
        $data->month = 12;
        $data->ccv_check = 123;

        $this->assertEquals(array(
            'creditcard_cctype' => 'VISA',
            'creditcard_name' => 'John Doe',
            'creditcard_ccno' => 4111111111111111,
            'creditcard_ccexpm' => 12,
            'creditcard_ccexpy' => '',
            'creditcard_cccvd' => 123,
        ), $card->mapToMyPaymentSchema($data));
    }
}