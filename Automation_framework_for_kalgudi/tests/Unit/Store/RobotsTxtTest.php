<?php

namespace Unit\Store;

class RobotsTxtTest extends \PHPUnit_Framework_TestCase
{
    public function getIsRequestForDeniedDomainData()
    {
        $data = array();

        $primaryDomain    = 'example.com';
        $wwwPrimaryDomain = 'www.example.com';
        $alternateDomain  = 'store-xyz.mybigcommerce.com';

        $customSslShopPath    = 'https://example.com';
        $wwwCustomSslShopPath = 'https://www.example.com';
        $sharedSslShopPath    = 'https://store-xyz.mybigcommerce.com';

        $httpRequest  = false;
        $httpsRequest = true;

        $isNotDenied = false;
        $isDenied    = true;

        $data[] = array(
            $primaryDomain,
            $customSslShopPath,
            $httpRequest,
            $primaryDomain,
            $isNotDenied,
        );

        $data[] = array(
            $primaryDomain,
            $customSslShopPath,
            $httpsRequest,
            $primaryDomain,
            $isNotDenied,
        );

        $data[] = array(
            $primaryDomain,
            $customSslShopPath,
            $httpRequest,
            $alternateDomain,
            $isDenied,
        );

        $data[] = array(
            $primaryDomain,
            $customSslShopPath,
            $httpsRequest,
            $alternateDomain,
            $isDenied,
        );


        $data[] = array(
            $primaryDomain,
            $sharedSslShopPath,
            $httpRequest,
            $primaryDomain,
            $isNotDenied,
        );

        $data[] = array(
            $primaryDomain,
            $sharedSslShopPath,
            $httpsRequest,
            $primaryDomain,
            $isDenied,
        );

        $data[] = array(
            $primaryDomain,
            $sharedSslShopPath,
            $httpRequest,
            $alternateDomain,
            $isDenied,
        );

        $data[] = array(
            $primaryDomain,
            $sharedSslShopPath,
            $httpsRequest,
            $alternateDomain,
            $isNotDenied,
        );


        //extra edge cases

        $data[] = array(
            $wwwPrimaryDomain,
            $customSslShopPath,
            $httpRequest,
            $primaryDomain,
            $isNotDenied,
        );

        $data[] = array(
            $primaryDomain,
            $customSslShopPath,
            $httpRequest,
            $wwwPrimaryDomain,
            $isNotDenied,
        );


        return $data;
    }

    /**
     * @dataProvider getIsRequestForDeniedDomainData
     */
    public function testIsRequestForDeniedDomain($primaryDomain, $shopPathSsl, $https, $requestHost, $expected)
    {
        $settings = $this->getMock('Store_Settings', array('get'));

        $settings->expects($this->at(0))
                 ->method('get')
                 ->with('PrimaryPleskDomain')
                 ->will($this->returnValue($primaryDomain));

        $settings->expects($this->at(1))
                 ->method('get')
                 ->with('ShopPathSSL')
                 ->will($this->returnValue($shopPathSsl));

        $request = $this->getMock('Interspire_Request', array('getHost', 'isHttps'));

        $request->expects($this->atLeastOnce())
                ->method('getHost')
                ->will($this->returnValue($requestHost));

        $request->expects($this->atLeastOnce())
                ->method('isHttps')
                ->will($this->returnValue($https));

        $actual = \Store_RobotsTxt::isRequestForDeniedDomain($request, $settings);

        $this->assertSame($expected, $actual);
    }
}
