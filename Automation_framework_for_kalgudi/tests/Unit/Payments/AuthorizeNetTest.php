<?php
namespace Unit\Payments;

class AuthorizeNetTest extends \PHPUnit_Framework_TestCase
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
        $card = new \CHECKOUT_AUTHORIZENET();

        $data = new \stdClass();
        $data->cardtype = 'VISA';
        $data->name = 'John Doe';
        $data->number = 4111111111111111;
        $data->month = 12;
        $data->year = 2012;
        $data->ccv_check = 123;

        $result = $card->mapToMyPaymentSchema($data);
        $this->assertEquals(array(
            'AuthorizeNet_cctype' => 'VISA',
            'AuthorizeNet_name' => 'John Doe',
            'AuthorizeNet_ccno' => 4111111111111111,
            'AuthorizeNet_ccexpm' => 12,
            'AuthorizeNet_ccexpy' => 2012,
            'AuthorizeNet_cccode' => 123,
        ), $result);

        $this->assertArrayNotHasKey('AuthorizeNet_cccvd', $result);
    }

    public function testMapToMyPaymentSchema_missingData()
    {
        $card = new \CHECKOUT_AUTHORIZENET();

        $data = new \stdClass();
        $data->cardtype = 'VISA';
        $data->name = 'John Doe';
        $data->number = 4111111111111111;
        $data->month = 12;
        $data->ccv_check = 123;

        $result = $card->mapToMyPaymentSchema($data);
        $this->assertEquals(array(
            'AuthorizeNet_cctype' => 'VISA',
            'AuthorizeNet_name' => 'John Doe',
            'AuthorizeNet_ccno' => 4111111111111111,
            'AuthorizeNet_ccexpm' => 12,
            'AuthorizeNet_ccexpy' => '',
            'AuthorizeNet_cccode' => 123,
        ), $result);
        $this->assertArrayNotHasKey('AuthorizeNet_cccvd', $result);
    }
}