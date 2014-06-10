<?php

namespace Unit\Lib\Store\Theme;

use \Store_Config;
use \Store\Theme\Asset as Asset;
use \Store\Theme\Context as Context;
use \org\bovigo\vfs\vfsStream;
use \PHPUnit_Framework_TestCase;

class AssetTest extends PHPUnit_Framework_TestCase
{
    private $asset;

    public function setUp()
    {
        $this->asset = new Asset();
        $this->asset
            ->setContext(null)
            ->setAppVersion('TEST-COMMIT-ID')
            ->setRepoBasePath(null)
            ->setTenantBasePath('vfs://tenant_home')
            ->setCdn($this->getMockCdn());

        // set default versioning configs
        Store_Config::override('template', 'test-theme');
        Store_Config::override('Feature_ThemeVersioning', true);
        Store_Config::override('ThemeVersionDesktop', array('test-version'));
    }

    public function tearDown()
    {
        Store_Config::revert('template');
        Store_Config::revert('Feature_ThemeVersioning');
        Store_Config::revert('ThemeVersionDesktop');
    }

    private function getMockCdn()
    {
        $cdn = $this->getMockBuilder('\Store_Cdn')
            ->setMethods(array('getApplicationUrl'))
            ->disableOriginalConstructor()
            ->getMock();

        $cdn->expects($this->any())
            ->method('getApplicationUrl')
            ->will($this->returnCallback('Unit\Lib\Store\Theme\mockGetApplicationUrlCallback'));

        return $cdn;
    }

    public function testGetRepoBasePathWithoutVersioning()
    {
        Store_Config::override('Feature_ThemeVersioning', false);
        $this->assertEquals(ISC_BASE_PATH . '/templates', $this->asset->getRepoBasePath());
    }

    public function testGetRepoBasePathWithVersioning()
    {
        $this->assertEquals("vfs://test_theme_root", $this->asset->getRepoBasePath());
    }

    public function pathProvider()
    {
        return array(
            array('', '/'),
            array('/', '/'),
            array('foo', '/foo'),
            array('/foo', '/foo'),
        );
    }

    /**
     * @dataProvider pathProvider
     */
    public function testGetRepoPathWhenVersioningFeatureOff($path, $expected)
    {
        Store_Config::override('Feature_ThemeVersioning', false);
        $this->assertEquals(rtrim(ISC_BASE_PATH . '/templates/test-theme/'. ltrim($expected, '/'), '/'),
            $this->asset->getRepoPath('test-theme', $path));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testGetRepoPathForCurrentThemeWhenVersionSet($path, $expected)
    {
        $this->assertEquals(rtrim("vfs://test_theme_root/test-theme/refs/test-version" . $expected, '/'),
            $this->asset->getRepoPath('test-theme', $path));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testGetRepoPathForCurrentThemeWhenVersionNotSet($path, $expected)
    {
        Store_Config::override('ThemeVersionDesktop', '');
        $this->assertEquals(rtrim("vfs://test_theme_root/test-theme/current" . $expected, '/'),
            $this->asset->getRepoPath('test-theme', $path));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testGetRepoPathForOtherTheme($path, $expected)
    {
        $this->assertEquals(rtrim("vfs://test_theme_root/other-theme/current" . $expected, '/'),
            $this->asset->getRepoPath('other-theme', $path));
    }

    public function testGetMasterThemePath()
    {
        $this->assertEquals('vfs://test_theme_root/__master/current/asset',
           $this->asset->getMasterThemePath('asset'));
    }

    public function testGetCurrentThemePath()
    {
        $this->assertEquals('vfs://test_theme_root/test-theme/refs/test-version',
           $this->asset->getCurrentThemePath());
    }

    /**
     * @dataProvider pathProvider
     */
    public function testGetTenantPath()
    {
        $this->assertEquals('vfs://tenant_home/test-theme/asset',
            $this->asset->getTenantPath('test-theme', 'asset'));
    }

    /**
     * @dataProvider pathProvider
     */
    public function testGetCustomThemePath()
    {
        $this->assertEquals('vfs://tenant_home/__custom/asset',
            $this->asset->getCustomThemePath('asset'));
    }

    public function testGetRepoURLWhenVersioningFeatureOff()
    {
        Store_Config::override('Feature_ThemeVersioning', false);
        $this->assertEquals('http://cdn_host/rTEST-COMMIT-ID/templates/test-theme/asset',
            $this->asset->getRepoURL('test-theme', 'asset'));
    }

    public function testGetRepoURLForCurrentThemeWhenVersionSet()
    {
        $this->assertEquals('http://cdn_host/rtest-version/themes/test-theme/asset',
            $this->asset->getRepoURL('test-theme', 'asset'));
    }

    public function testGetRepoURLForCurrentThemeWhenVersionNotSet()
    {
        Store_Config::override('ThemeVersionDesktop', '');
        $this->deployThemeWithVersion('test-theme');
        $this->assertEquals('http://cdn_host/rhead-version/themes/test-theme/asset',
            $this->asset->getRepoURL('test-theme', 'asset'));
    }

    public function testGetRepoURLForOtherTheme()
    {
        $this->deployThemeWithVersion('other-theme');
        $this->assertEquals('http://cdn_host/rhead-version/themes/other-theme/asset',
            $this->asset->getRepoURL('other-theme', 'asset'));
    }

    public function testGetMasterThemeURL()
    {
        $this->deployThemeWithVersion('__master');
        $this->assertEquals('http://cdn_host/rhead-version/themes/__master/asset',
            $this->asset->getMasterThemeURL('asset'));
    }

    public function testGetCurrentThemeURL()
    {
        $this->deployThemeWithVersion('test-theme');
        $this->assertEquals('http://cdn_host/rtest-version/themes/test-theme/asset',
            $this->asset->getCurrentThemeURL('asset'));
    }

    public function testIsNotStreambleForNonMatchPattern()
    {
        $this->assertFalse($this->asset->isStreamable("#\.css$#", "foo.js"));
    }

    public function streamableFeatureProvider()
    {
        return array(
            array(false, false, false, false),
            array(false, false, true , false),
            array(false, true, false, false),
            array(false, true, true, true),
            array(true, false, false, true),
        );
    }

    /**
     * @dataProvider streamableFeatureProvider
     */
    public function testIsStreamableWithConfig($stream, $cdnStoreAsset, $cdnStoreCss, $result)
    {
        Store_Config::override('StreamCustomCss', $stream);
        Store_Config::override('Feature_CdnStoreAsset', $cdnStoreAsset);
        Store_Config::override('Feature_CdnStoreCss', $cdnStoreCss);

        $this->assertEquals($result, $this->asset->isStreamable("#\.css$#", "foo.css"));

        Store_Config::revert('StreamCustomCss');
        Store_Config::revert('Feature_CdnStoreAsset');
        Store_Config::revert('Feature_CdnStoreCss');
    }

    private function mockFingerprinter($relativePath)
    {
        $mock = $this->getMock('\Store\Theme\Fingerprint', array('generateUrl'));
        $mock->expects($this->once())
            ->method('generateUrl')
            ->with($this->equalTo($relativePath))
            ->will($this->returnValue("http://cdn-host/{$relativePath}"));

        return $mock;
    }

    public function ignoredCustomAssetProvider()
    {
        return array(
            // not supported extension type
            array(
                "<img src=\"%%ASSET_images/abc.invalid%%\" />",
            ),
            // missing closing %%
            array(
                "<img src=\"%%ASSET_images/abc.jpg\" />",
            ),
            // missing opening %%
            array(
                "<img src=\"ASSET_images/abc.jpg%%\" />",
            ),
            // no %% at all
            array(
                "<img src=\"ASSET_images/abc.jpg\" />",
            ),
        );
    }

    public function notStreamableCustomAssetProvider()
    {
        return array(
            array(
                "<img src=\"%%ASSET_images/abc.jpg%%\" />",
                "templates/__custom/images/abc.jpg",
                "<img src=\"http://cdn-host/templates/__custom/images/abc.jpg\" />",
            ),
            array(
                "background: url(%%ASSET_images/abc.png%%);",
                "templates/__custom/images/abc.png",
                "background: url(http://cdn-host/templates/__custom/images/abc.png);",
            ),
            array(
                "<script src=\"%%ASSET_js/test.js%%\" />",
                "templates/__custom/js/test.js",
                "<script src=\"http://cdn-host/templates/__custom/js/test.js\" />",
            ),
        );
    }

    public function streamableCustomAssetProvider()
    {
        return array(
            array(
                "<link href=\"%%ASSET_Styles/styles.css%%\" />",
                "templates/__custom/Styles/styles.css",
                "<link href=\"http://cdn-host/templates/__custom/Styles/styles.css\" />",
            ),
            array(
                "<link href=\"%%ASSET_Styles/my/foo.otf%%\" />",
                "templates/__custom/Styles/my/foo.otf",
                "<link href=\"http://cdn-host/templates/__custom/Styles/my/foo.otf\" />",
            ),
        );
    }

    /**
     * @dataProvider ignoredCustomAssetProvider
     */
    public function testParseIgnoredCustomAssetTemplate($html)
    {
        $mock = $this->getMock('\Store\Theme\Fingerprint', array('generateUrl'));
        $mock->expects($this->never())
            ->method('generateUrl');

        $this->asset->setFingerprint($mock);
        $this->assertEquals($html, $this->asset->parseCustomAssetTemplate($html));
    }

    /**
     * @dataProvider notStreamableCustomAssetProvider
     */
    public function testParseNotStreamableCustomAssetTemplate($html, $url, $expected)
    {
        $this->asset->setFingerprint($this->mockFingerprinter($url));
        $this->assertEquals($expected, $this->asset->parseCustomAssetTemplate($html));
    }

    /**
     * @dataProvider streamableCustomAssetProvider
     */
    public function testParseStreamableCustomAssetTemplate($html, $url, $expected)
    {
        Store_Config::override('Feature_CdnStoreAsset', true);
        Store_Config::override('Feature_CdnStoreCss', true);

        $this->asset->setFingerprint($this->mockFingerprinter($url));
        $this->assertEquals($expected, $this->asset->parseCustomAssetTemplate($html));

        Store_Config::revert('Feature_CdnStoreAsset');
        Store_Config::revert('Feature_CdnStoreCss');
    }

    private function setupVirtualThemeDeploymentDirectory($theme)
    {
        vfsStream::setup('test_theme_root');
        $root = vfsStream::url('test_theme_root');

        mkdir($root . "/{$theme}");
        mkdir($root . "/{$theme}/current");
        mkdir($root . "/{$theme}/current/config");

        return "{$root}/{$theme}";
    }

    private function deployThemeWithVersion($theme, $version = "head-version")
    {
        $root = $this->setupVirtualThemeDeploymentDirectory($theme);
        file_put_contents("{$root}/current/REVISION", $version);
    }

    private function setupCustomizationFolder($path = '')
    {
        vfsStream::setup('tenant_home');
        $root = vfsStream::url('tenant_home');

        mkdir($root . "/__custom");
        mkdir($root . "/__custom/Emails");
        mkdir($root . "/__custom/GiftThemes");
        mkdir($root . "/__custom/Panels");
        mkdir($root . "/__custom/Snippets");
        mkdir($root . "/__custom/Styles");
        mkdir($root . "/__custommobile");
        mkdir($root . "/__custommobile/Panels");
        mkdir($root . "/__custommobile/Snippets");
        mkdir($root . "/__custommobile/Styles");

        if($path) {
            file_put_contents($root . $path, "new content");
        }
    }

    public function testHasNoCustomization()
    {
        $this->setupCustomizationFolder();
        $this->assertFalse($this->asset->hasCustomization());
    }

    public function desktopCustomizationDataProvider()
    {
        return array(
            array('/__custom/new.html'),
            array('/__custom/Emails/new.html'),
            array('/__custom/GiftThemes/new.html'),
            array('/__custom/Panels/new.html'),
            array('/__custom/Snippets/new.html'),
            array('/__custom/Styles/new.css'),
        );
    }

    public function mobileCustomizationDataProvider()
    {
        return array(
            array('/__custommobile/new.html'),
            array('/__custommobile/Panels/new.html'),
            array('/__custommobile/Snippets/new.html'),
            array('/__custommobile/Styles/new.css'),
        );
    }

    /**
     * @dataProvider desktopCustomizationDataProvider
     */
    public function testHasCustomizationOnDesktop($filePath)
    {
        $this->setupCustomizationFolder($filePath);
        $this->assertTrue($this->asset->hasCustomization());
    }

    /**
     * @dataProvider mobileCustomizationDataProvider
     */
    public function testHasCustomizationOnMobile($filePath)
    {
        $this->asset->setContext(new Context\Mobile());
        $this->setupCustomizationFolder($filePath);
        $this->assertTrue($this->asset->hasCustomization());
    }
}

function mockGetApplicationUrlCallback($path)
{
    return "http://cdn_host/rTEST-COMMIT-ID/{$path}";
}
