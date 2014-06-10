<?php

class Unit_Modules_Checkout_Masterpass extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!isset($GLOBALS['ShopPath'])) {
            $GLOBALS['ShopPath'] = '';
        }
    }

    public function testOAuthRequest()
    {
        $wrapper = new MasterpassWrapper();
         
        $this->assertEquals(true, $wrapper->isDigitalWallet());
        
        $method = new ReflectionMethod(
           'MasterpassWrapper', '_initOAuthRequest'
        );
 
        $method->setAccessible(TRUE); 

        $method->invoke($wrapper);

        $masterpassGateway = $wrapper->getGateway();

        $this->assertInstanceOf("\Services\Payments\MasterPass\MasterPass",$masterpassGateway);
         
        $expectedCards = "visa,master,amex,diners,discover,maestro,cup,jcb";
         
        $this->assertEquals($expectedCards, $masterpassGateway->acceptableCards);
    }
}

class MasterpassWrapper extends CHECKOUT_MASTERPASS {
	
    public $moduleVariables;
    
    public function __construct() {
		$this->moduleVariables = array("payment_provider"=>"checkout_stipe"); 
	}
    
    public function GetValue($key)
    {
        switch ($key) {
            case 'MasterPass_testmode':
                return 'YES';
                break;
            
            case 'masterpass_sandbox_consumer_key':
                return "123456";
                break;
            
            case 'payment_provider':
                return 'checkout_stripe';
                break;
            
            case 'checkout_identifier':
                return '123456';
                break;
            
            default:
                return $key;
                break;
        }       
        
    }
    
}
