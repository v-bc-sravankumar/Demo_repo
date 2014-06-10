<?php

class Unit_Lib_Store_Cdn extends Interspire_UnitTest
{
    protected $_hostnameDefault = 'cdn.bigcommerce.net';

    protected $_hostnameCss = 'css.cdn.bigcommerce.net';

    protected $_hostnameJs = 'js.cdn.bigcommerce.net';

    protected $_storeUrl = ':protocol://:cdn_host/bc/:shop_path/:server_name/:store_id';

    protected $_versionUrl = ':protocol://:cdn_host/bc/:shop_path/v:version_code/:build_code';

    protected $_buildCode = 'deadbeef';

    /**
     * @var Store_Cdn_Environment
     */
    protected $_testEnvironment = null;

    protected $_serverName;

    protected $_appPath;

    protected $_storePath;

    protected $_shopPath;

    protected $_shopPathNormal;

    protected $_shopPathSSL;

    protected $_backupHostnames;

    protected $_backupStoreUrl;

    protected $_backupVersionUrl;

    protected $_backupEnabled;

    protected $_backupHttps;

    protected $originalConfig;

    public function setUp()
    {
        $this->_backupHostnames = Interspire_Environment::get('BC_CDN_HOSTNAMES');
        $this->_backupEnabled = Interspire_Environment::get('BC_CDN_DISABLED');

        $this->_backupStoreUrl = Interspire_Environment::get('BC_CDN_URL_STORE');
        $this->_backupVersionUrl = Interspire_Environment::get('BC_CDN_URL_VERSION');

        $this->originalConfig = array(
            'ShopPath' => Store_Config::get('ShopPath'),
            'ShopPathSSL' => Store_Config::get('ShopPathSSL'),
            'AppPath' => Store_Config::get('AppPath'),
            'HostingId' => Store_Config::get('HostingId'),
        );

        $this->_shopPath = 'http://www.foobar.com';
        $this->_shopPathNormal = 'http://www.foo.com';
        $this->_shopPathSSL = 'https://www.bar.com';

        Store_Config::override('ShopPath', $this->_shopPath);
        Store_Config::override('ShopPathSSL', $this->_shopPathSSL);
        Store_Config::override('ShopPathNormal', $this->_shopPathNormal);
        Store_Config::override('HostingId', 987654321);

        $this->_backupHttps = $_SERVER['HTTPS'];
        Store_Config::override('Feature_CdnCommonAdmin', 1);
        Store_Config::override('Feature_CdnCommonStoreFront', 1);
        Store_Config::override('Feature_CdnStoreAsset', 1);
        Store_Config::override('Feature_CdnStoreProductImages', 1);

        putenv('BC_CDN_HOSTNAMES=' . http_build_query(array(
            'default' => $this->_hostnameDefault,
            'js' => $this->_hostnameJs,
            'css' => $this->_hostnameCss,
        )));
        putenv('BC_CDN_URL_STORE=' . $this->_storeUrl);
        putenv('BC_CDN_URL_VERSION=' . $this->_versionUrl);
        putenv('BC_CDN_DISABLED=0');

        $_SERVER['HTTPS'] = 'off';

        $this->_testEnvironment = Store_Cdn_Environment::generateDefault();
        $this->_testEnvironment->setBuildCode($this->_buildCode);
        $this->_serverName = $this->_testEnvironment->getServerName();
        $this->_appPath = '/bc/'.$this->_testEnvironment->getShopPath().'/v' . PRODUCT_VERSION_CODE . '/' . $this->_buildCode;
        $this->_storePath = '/bc/'.$this->_testEnvironment->getShopPath().'/'.$this->_serverName.'/' . Store_Config::get('HostingId');
    }

    public function tearDown()
    {
        foreach ($this->originalConfig as $setting => $value) {
            Store_Config::override($setting, $value);
        }

        putenv('BC_CDN_HOSTNAMES=' . $this->_backupHostnames);
        putenv('BC_CDN_DISABLED=' . (int)$this->_backupEnabled);
        putenv('BC_CDN_URL_STORE=' . $this->_backupStoreUrl);
        putenv('BC_CDN_URL_VERSION=' . $this->_backupVersionUrl);
        $_SERVER['HTTPS'] = $this->_backupHttps;
    }

    public function testGetDefaultCdnHostname()
    {
        $cdn = new Store_Cdn($this->_testEnvironment);
        $this->assertSame($cdn->getDefaultCdnHostname(), $this->_hostnameDefault);
    }

    public function testCdnAvailable()
    {
        $cdn = new Store_Cdn($this->_testEnvironment);
        $this->assertTrue($cdn->useCdn());
    }

    public function testCdnUnavailable()
    {
        putenv('BC_CDN_DISABLED=1');

        $cdn = new Store_Cdn($this->_testEnvironment);
        $this->assertFalse($cdn->useCdn());
    }

    public function testGetApplicationUrlCdnCurrentProtocolHttp()
    {
        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'http://' . $this->_hostnameJs . $this->_appPath . '/javascript/common.js';
        $this->assertSame($expected, $cdn->buildCDNUrl('javascript/common.js'));
    }

    public function testGetApplicationUrlCdnCurrentProtocolHttps()
    {
        $_SERVER['HTTPS'] = 'on';

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'https://' . $this->_hostnameJs . $this->_appPath . '/javascript/common.js';
        $this->assertSame($expected, $cdn->buildCDNUrl('javascript/common.js'));
    }

    public function testGetApplicationUrlCdnForceHttp()
    {
        // set current environment to HTTPS to ensure we're forcing back to HTTP
        $_SERVER['HTTPS'] = 'on';

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'http://' . $this->_hostnameJs . $this->_appPath . '/javascript/common.js';
        $this->assertSame($expected, $cdn->buildCDNUrl('javascript/common.js', Store_Cdn::HTTP));
    }

    public function testGetApplicationUrlCdnForceHttps()
    {
        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'https://' . $this->_hostnameJs . $this->_appPath . '/javascript/common.js';
        $this->assertSame($expected, $cdn->buildCDNUrl('javascript/common.js', Store_Cdn::SSL));
    }

    public function testGetApplicationUrlLocal()
    {
        putenv('BC_CDN_DISABLED=1');

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = $this->_shopPath . '/javascript/common.js?key=' . Store_Config::get('JSCacheToken');
        $this->assertSame($expected, $cdn->buildCDNUrl('javascript/common.js'));
    }


    public function testGetApplicationUrlLocalForceHttp()
    {
        putenv('BC_CDN_DISABLED=1');
        // set current environment to HTTPS to ensure we're forcing back to HTTP
        $_SERVER['HTTPS'] = 'on';

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = $this->_shopPathNormal . '/javascript/common.js?key=' . Store_Config::get('JSCacheToken');
        $this->assertSame($expected, $cdn->buildCDNUrl('javascript/common.js', Store_Cdn::HTTP));
    }

    public function testGetApplicationUrlLocalForceHttps()
    {
        putenv('BC_CDN_DISABLED=1');

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = $this->_shopPathSSL . '/javascript/common.js?key=' . Store_Config::get('JSCacheToken');
        $this->assertSame($expected, $cdn->buildCDNUrl('javascript/common.js',Store_Cdn::SSL));
    }

    public function testGetStoreUrlCdnCurrentProtocolHttp()
    {
        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'http://' . $this->_hostnameDefault . $this->_storePath . '/product_images/a/123.jpg';
        $this->assertSame($expected, $cdn->buildCDNUrl('product_images/a/123.jpg'));
    }

    public function testGetStoreUrlCdnCurrentProtocolHttps()
    {
        $_SERVER['HTTPS'] = 'on';

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'https://' . $this->_hostnameDefault . $this->_storePath . '/product_images/a/123.jpg';
        $this->assertSame($expected, $cdn->buildCDNUrl('product_images/a/123.jpg'));
    }

    public function testGetStoreUrlCdnForceHttp()
    {
        // set current environment to HTTPS to ensure we're forcing back to HTTP
        $_SERVER['HTTPS'] = 'on';

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'http://' . $this->_hostnameDefault . $this->_storePath . '/product_images/a/123.jpg';
        $this->assertSame($expected, $cdn->buildCDNUrl('product_images/a/123.jpg', Store_Cdn::HTTP));
    }

    public function testGetStoreUrlCdnForceHttps()
    {
        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'https://' . $this->_hostnameDefault . $this->_storePath . '/product_images/a/123.jpg';
        $this->assertSame($expected, $cdn->buildCDNUrl('product_images/a/123.jpg', Store_Cdn::SSL));
    }

    public function testGetStoreUrlLocal()
    {
        putenv('BC_CDN_DISABLED=1');

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = $this->_shopPath . '/product_images/a/123.jpg';
        $this->assertSame($expected, $cdn->buildCDNUrl('product_images/a/123.jpg'));
    }

    public function testGetStoreUrlLocalForceHttp()
    {
        putenv('BC_CDN_DISABLED=1');
        // set current environment to HTTPS to ensure we're forcing back to HTTP
        $_SERVER['HTTPS'] = 'on';

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = $this->_shopPathNormal . '/product_images/a/123.jpg';
        $this->assertSame($expected, $cdn->buildCDNUrl('product_images/a/123.jpg',Store_Cdn::HTTP));
    }

    public function testGetStoreUrlLocalForceHttps()
    {
        putenv('BC_CDN_DISABLED=1');

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = $this->_shopPathSSL . '/product_images/a/123.jpg';
        $this->assertSame($expected, $cdn->buildCDNUrl('product_images/a/123.jpg',Store_Cdn::SSL));
    }

    public function testGetCdnApplicationPathCurrentProtocolHttp()
    {
        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'http://' . $this->_hostnameDefault . $this->_appPath;
        $this->assertSame($expected, $cdn->getCdnApplicationPath());
    }

    public function testGetCdnApplicationPathCurrentProtocolHttps()
    {
        $_SERVER['HTTPS'] = 'on';

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'https://' . $this->_hostnameDefault . $this->_appPath;
        $this->assertSame($expected, $cdn->getCdnApplicationPath());
    }

    public function testGetCdnApplicationPathForceHttp()
    {
        // set current environment to HTTPS to ensure we're forcing back to HTTP
        $_SERVER['HTTPS'] = 'on';

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'http://' . $this->_hostnameDefault . $this->_appPath;
        $this->assertSame($expected, $cdn->getCdnApplicationPath(Store_Cdn::HTTP));
    }

    public function testGetCdnApplicationPathForceHttps()
    {
        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'https://' . $this->_hostnameDefault . $this->_appPath;
        $this->assertSame($expected, $cdn->getCdnApplicationPath(Store_Cdn::SSL));
    }

    public function testGetCdnStorePathCurrentProtocolHttp()
    {
        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'http://' . $this->_hostnameDefault . $this->_storePath;
        $this->assertSame($expected, $cdn->getCdnStorePath());
    }

    public function testGetCdnStorePathCurrentProtocolHttps()
    {
        $_SERVER['HTTPS'] = 'on';

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'https://' . $this->_hostnameDefault . $this->_storePath;
        $this->assertSame($expected, $cdn->getCdnStorePath());
    }

    public function testGetCdnStorePathForceHttp()
    {
        // set current environment to HTTPS to ensure we're forcing back to HTTP
        $_SERVER['HTTPS'] = 'on';

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'http://' . $this->_hostnameDefault . $this->_storePath;
        $this->assertSame($expected, $cdn->getCdnStorePath(Store_Cdn::HTTP));
    }

    public function testGetCdnStorePathForceHttps()
    {
        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'https://' . $this->_hostnameDefault . $this->_storePath;
        $this->assertSame($expected, $cdn->getCdnStorePath(Store_Cdn::SSL));
    }

    public function testCdnStoreAssetFlagOff()
    {

        Store_Config::override('Feature_CdnStoreAsset', 0);

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = $this->_shopPath . '/templates/__custom/default.css';
        $this->assertSame($expected, $cdn->buildCDNUrl('templates/__custom/default.css'));

        Store_Config::override('Feature_CdnStoreAsset', 1);
    }


    public function testCdnProductImagesFlagOff()
    {

        Store_Config::override('Feature_CdnStoreProductImages', 0);

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = $this->_shopPath . '/product_images/a/123.jpg';
        $this->assertSame($expected, $cdn->buildCDNUrl('product_images/a/123.jpg'));

        Store_Config::override('Feature_CdnStoreProductImages', 1);
    }

    public function testCdnStoreAssetFlagOn()
    {

        Store_Config::override('Feature_CdnStoreAsset', 1);

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'http://' . $this->_hostnameJs . $this->_storePath . '/templates/__custom/custom.js';
        $this->assertSame($expected, $cdn->buildCDNUrl('templates/__custom/custom.js'));

    }


    public function testCdnProductImagesFlagOn()
    {

        Store_Config::override('Feature_CdnStoreProductImages', 1);

        $cdn = new Store_Cdn($this->_testEnvironment);
        $expected = 'http://' . $this->_hostnameDefault . $this->_storePath . '/product_images/a/123.jpg';
        $this->assertSame($expected, $cdn->buildCDNUrl('product_images/a/123.jpg'));

    }

    public function excludePathProvider()
    {
        return array(
            array('cache/foo', false, true),
            array('templates/__custom/foo.css', false, true),
            array('templates/__custom/foo.css', true, false),
            array('template/foo.css', false, true),
            array('template/foo.css', true, false),
            array('templates/__custommobile/foo.css', false, true),
            array('templates/__custommobile/foo.css', true, false),
            array('mobile_template/foo.css', false, true),
            array('mobile_template/foo.css', true, false),
            array('templates/__custom/bar.js', false, false),
            array('templates/__custom/bar.js', true, false),
            array('templates/__custom/bar.jpg', false, false),
            array('templates/__custom/bar.jpg', true, false),
        );
    }

    /**
     * @dataProvider excludePathProvider
     */
    public function testIsExluded($path, $cdnStoreCssEnabled, $expected)
    {
        Store_Config::override('Feature_CdnStoreCss', $cdnStoreCssEnabled);

        $cdn = new Store_Cdn($this->_testEnvironment);
        $this->assertEquals($expected, $cdn->isExcluded($path));

        Store_Config::revert('Feature_CdnStoreCss');
    }

    public function storeAssetPathProvider()
    {
        return array(
            array(Store_Config::get('ImageDirectory')),
            array("products/abc123/images/abc123/"),
            array("templates/__custom/foo"),
            array("template/foo"),
            array("templates/__custommobile/foo"),
            array("mobile_template/foo"),
        );
    }

    /**
     * @dataProvider storeAssetPathProvider
     */
    public function testIsStoreAssetPath($path)
    {
        $cdn = new Store_Cdn($this->_testEnvironment);
        $this->assertTrue($cdn->isStoreAssetPath($path));
    }
}
