<?php

class Unit_Interspire_AuthTest extends PHPUnit_Framework_TestCase
{
    private $_configMock;
    private $_requestMock;
    private $_responseMock;

    public function setUp()
    {
        $this->_configMock = $this->getMockClass('Store_Config', array('get'));

        $this->_requestMock = $this
            ->getMockBuilder('Interspire_Request')
            ->disableOriginalConstructor()
            ->setMethods(array('getResponse'))
            ->getMock();

        $this->_responseMock = $this
            ->getMockBuilder('Interspire_Response')
            ->disableOriginalConstructor()
            ->setMethods(array('redirect'))
            ->getMock();

        $this->_requestMock
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->_responseMock));
    }

    public function testWhenGiftCertificatesEnabled()
    {
        // When gift certificates are enabled, the manage gift certificates page should be shown and the user should not
        // be redirected.

        // Make sure redirect is not called.
        $this->_responseMock
            ->expects($this->exactly(0))
            ->method('redirect');

        $configMock = $this->_configMock;
        $configMock::staticExpects($this->at(0))
            ->method('get')
            ->with('EnableGiftCertificates')
            ->will($this->returnValue(1));

        $giftCertificatesMock = $this
            ->getMockBuilder('ISC_ADMIN_GIFTCERTIFICATES')
            ->disableOriginalConstructor()
            ->setMethods(array('HandleToDo'))
            ->getMock();

        $giftCertificatesMock
            ->expects($this->once())
            ->method('HandleToDo')
            ->with('viewGiftCertificates');

        $auth = new TestAdminAuth();
        $auth->setRequest($this->_requestMock);
        $auth->HandleSTSToDo('viewGiftCertificates', $configMock, $giftCertificatesMock);
    }

    public function testWhenGiftCertificatesDisabled()
    {
        // When gift certificates are disabled, the manage gift certificates page should not be shown and the user
        // should be redirected to the page where he or she can enable gift certificates.

        $this->_responseMock
            ->expects($this->exactly(1))
            ->method('redirect');

        $configMock = $this->_configMock;
        $configMock::staticExpects($this->at(0))
            ->method('get')
            ->with('EnableGiftCertificates')
            ->will($this->returnValue(0));
        $configMock::staticExpects($this->at(1))
            ->method('get')
            ->with('ShopPathSSL')
            ->will($this->returnValue('https://test-path'));

        $giftCertificatesMock = $this
            ->getMockBuilder('ISC_ADMIN_GIFTCERTIFICATES')
            ->disableOriginalConstructor()
            ->setMethods(array('HandleToDo'))
            ->getMock();

        // Ensure HandleToDo is not called.
        $giftCertificatesMock
            ->expects($this->exactly(0))
            ->method('HandleToDo');

        $auth = new TestAdminAuth();
        $auth->setRequest($this->_requestMock);
        $auth->HandleSTSToDo('viewGiftCertificates', $configMock, $giftCertificatesMock, false);
    }
}

if (!function_exists('GetLib')) {
    function GetLib($lib)
    {
        // Do nothing. This is called at the top of 'class.giftcertificates.php'.
    }
}

class TestAdminAuth extends ISC_ADMIN_AUTH
{
    public function __construct()
    {
        // Do nothing. Just used to disable original constructor.
    }
}

