<?php

class Integration_Modules_Checkout_PayPalExpress extends PHPUnit_Framework_TestCase {
    public function testLoadClass()
    {
        $gateway = new CHECKOUT_PAYPALEXPRESS();
    }

    public function testDoExpressCheckoutPaymentHandlesSessionTimeout()
    {
        $gatewayMock = $this->getMockBuilder('\CHECKOUT_PAYPALEXPRESS')->setMethods(array(
            'GetOrders', 'GetClientSettings', 'isOrderTokenSet', '_ConnectToProvider', '_DecodePaypalResult',
            'SetCheckoutData', 'getResponseFromSession', 'GetIpAddress', 'getShippingAddress',
        ))->getMock();

        $gatewayMock->expects($this->once())->method('isOrderTokenSet')->will($this->returnValue(true));

        $gatewayMock->expects(($this->once()))->method('GetOrders')->will($this->returnValue(array(1 => array(
                'total_inc_tax' => 12.99,
            ),
        )));

        $gatewayMock->expects($this->once())->method('_DecodePaypalResult')->will($this->returnValue(array(
           'L_ERRORCODE0' => CHECKOUT_PAYPALEXPRESS::ERROR_EXPIRED_SESSION,
        )));

        $gatewayMock->expects($this->once())->method('getResponseFromSession')->will($this->returnValue(array(
            'TOKEN' => '1000000', 'PAYERID' => '7',
        )));

        $gatewayMock->expects($this->once())->method('GetClientSettings')->will($this->returnValue(array(
            'username' => 'test',
            'password' => 'test',
            'signature' => 'test',
            'transactionType' => 'test',
        )));

        $gatewayMock->expects($this->once())->method('GetIpAddress')->will($this->returnValue('127.0.0.1'));

        $gatewayMock->expects($this->once())->method('getShippingAddress')->will($this->returnValue(array(
            'first_name',
            'last_name',
            'address_1',
            'address_2',
            'country_iso2',
            'phone',
        )));

        $gatewayMock->expects($this->once())->method('SetCheckoutData');

        $gatewayMock->DoExpressCheckoutPayment();


    }

}
