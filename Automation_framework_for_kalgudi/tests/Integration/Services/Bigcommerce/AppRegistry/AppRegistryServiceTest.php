<?php

namespace Integration\Services\Bigcommerce\AppRegistry;

use Platform\Account;
use Store_Config;
use Services\Bigcommerce\AppRegistry\Entity\Review;

class AppRegistryServiceTest extends \Interspire_IntegrationTest
{

    /**
     * @var AppRegistryServiceHelper
     */
    protected $appRegistryServiceHelper = null;
    protected $originalConfigs = array();

    public function __construct()
    {
        $this->appRegistryServiceHelper = new AppRegistryServiceHelper();
    }

    public function setUp()
    {
        $this->originalConfigs['StoreHash'] = Store_Config::get('StoreHash');
        Store_Config::override('StoreHash', 'test.store', true);
    }

    public function tearDown()
    {
        $this->appRegistryServiceHelper->resetDataStore();
        // reload config after all the munging
        foreach ($this->originalConfigs as $key => $value) {
            Store_Config::override($key, $value);
        }

    }

    /**
     * @return \Services\Bigcommerce\AppRegistry\AppRegistryService
     */
    protected function getAppRegistryService()
    {
        return $this->appRegistryServiceHelper->createAppRegistryService();
    }

    public function testGetApplications()
    {
        $expected = $this->appRegistryServiceHelper->createRandomApplication();

        $service = $this->getAppRegistryService();

        $apps = $service->getApplications();
        $this->assertEquals(1, count($apps['apps']));
        $app = current($apps['apps']);
        $this->assertEquals($expected, $app->toArray());

    }

    public function testGetInstalledApplications()
    {
        $app1 = $this->appRegistryServiceHelper->createRandomApplication();
        $app2 = $this->appRegistryServiceHelper->createRandomApplication();

        $expected = $app1;

        $this->appRegistryServiceHelper->createRandomInstall($app1['id'], 1, 'installed');
        $this->appRegistryServiceHelper->createRandomInstall($app2['id'], 1, 'installing');

        $service = $this->getAppRegistryService();

        $apps = $service->getApplications(array('installed' => true));
        $this->assertEquals(1, count($apps['apps']));
        $app = current($apps['apps']);
        $this->assertEquals($expected, $app->toArray());
    }

    public function testGetInstalls()
    {

        $this->appRegistryServiceHelper->createRandomApplication();
        $this->appRegistryServiceHelper->createRandomApplication();

        $expected = array(
            $this->appRegistryServiceHelper->createRandomInstall(1, 1, 'installed'),
            $this->appRegistryServiceHelper->createRandomInstall(2, 1, 'installing'),
        );

        $service = $this->getAppRegistryService();

        $installs = $service->getInstalls();

        $this->assertEquals($expected, array_map(function($install) {
           return $install->toArray();
        }, $installs));

    }

    public function testGetInstallById()
    {

        $expectedInstalls = array();
        for ($i = 0; $i < 5; $i++) {
            $app = $this->appRegistryServiceHelper->createRandomApplication();
            $expectedInstalls[] = $this->appRegistryServiceHelper->createRandomInstall($app['id'], rand(1, 5), 'installed');
        }

        $service = $this->getAppRegistryService();
        foreach ($expectedInstalls as $expected) {
            $install = $service->getInstallById($expected['id']);
            $this->assertNotEmpty($install);
            $this->assertEquals($expected['id'], $install->getId());
        }

    }

    public function testInstallApplication()
    {
        $app = $this->appRegistryServiceHelper->createRandomApplication();
        $service = $this->getAppRegistryService();
        $resp = $service->installApplication($app['id'], 1, 'share_code');

        $expected = current($this->appRegistryServiceHelper->dataStore['installs']);

        $this->assertArrayHasKey('install', $resp);
        $this->assertArrayHasKey('auth_url', $resp);

        $install = $resp['install'];
        $this->assertEquals($expected, $install->toArray());
    }

    public function testUninstallApplication()
    {
        $app = $this->appRegistryServiceHelper->createRandomApplication();
        $expected = $this->appRegistryServiceHelper->createRandomInstall($app['id'], 1, 'installed');

        $service = $this->getAppRegistryService();
        $resp = $service->uninstallApplication($app['id'], 1);
        $this->assertArrayHasKey('install', $resp);

        $install = $resp['install'];
        $this->assertEquals($expected, $install->toArray());

    }

    public function testLoadApplicationWhenInstalled()
    {
        $app = $this->appRegistryServiceHelper->createRandomApplication();
        $this->appRegistryServiceHelper->createRandomInstall($app['id'], 1, 'installed');

        $service = $this->getAppRegistryService();
        $resp = $service->loadApplication($app['id'], 1);
        $this->assertArrayHasKey('authorized', $resp);
        $this->assertTrue($resp['authorized']);
        $this->assertArrayHasKey('url', $resp);
        $this->assertEquals($app['load_url'], $resp['url']);

    }

    public function testLoadDeepLinkWhenInstalled()
    {
        $app = $this->appRegistryServiceHelper->createRandomApplication();
        $this->appRegistryServiceHelper->createRandomInstall($app['id'], 1, 'installed');
        $deepLink = $app['deep_links'][0];

        $service = $this->getAppRegistryService();
        $resp = $service->loadApplication($app['id'], 1, array('deep_link' => $deepLink['id']));
        $this->assertArrayHasKey('authorized', $resp);
        $this->assertTrue($resp['authorized']);
        $this->assertArrayHasKey('url', $resp);
        $this->assertEquals($deepLink['url'], $resp['url']);

    }

    public function testLoadApplicationWhenNotInstalled()
    {
        $app = $this->appRegistryServiceHelper->createRandomApplication();
        $this->appRegistryServiceHelper->createRandomInstall($app['id'], 2, 'installed');

        $service = $this->getAppRegistryService();
        $resp = $service->loadApplication($app['id'], 1);
        $this->assertArrayHasKey('authorized', $resp);
        $this->assertFalse($resp['authorized']);
    }

    public function testGetAllReviewsForApp()
    {
        $appId = rand(0, 1000);
        $expected = $this->appRegistryServiceHelper->createRandomReview($appId);

        $service = $this->getAppRegistryService();

        $reviews = $service->getReviews($appId, array());
        $this->assertEquals(1, count($reviews));
        $review = current($reviews);
        $this->assertEquals($expected, $review->toArray());
    }

    public function testGetReviewForApp()
    {
        $appId = rand(0, 1000);
        $expected = $this->appRegistryServiceHelper->createRandomReview($appId);

        $service = $this->getAppRegistryService();

        $review = $service->getReview($appId, $expected['id']);
        $this->assertEquals($expected, $review->toArray());
    }

    public function testCreateReview()
    {
        $appId = rand(0, 1000);
        $service = $this->getAppRegistryService();
        $expect = array(
            'rating' => 5,
            'subject' => 'Test Subject',
            'content' => 'Test Content',
            'website' => 'http://test.com'
        );

        $review = $service->createReview($appId, new Review($expect));

        $actual = array(
            'rating' => $review['rating'],
            'subject' => $review['subject'],
            'content' => $review['content'],
            'website' => $review['website']
        );

        $this->assertEquals($expect, $actual);
        $this->assertArrayHasKey('id', $review);
        $this->assertArrayHasKey('name', $review);
        $this->assertEquals($appId, $review['application_id']);
    }

    public function testUpdateReview()
    {
        $appId = rand(0, 1000);
        $expect = $this->appRegistryServiceHelper->createRandomReview($appId);
        $service = $this->getAppRegistryService();

        $expect['rating'] = 2;
        $expect['subject'] = 'Updated Subject';
        $expect['content'] = 'Updated Content';
        $expect['website'] = 'http://test.com/updated';

        $updatedReview = $service->updateReview($appId, new Review($expect));

        $this->assertEquals($expect, $updatedReview);
    }

    public function testGetCategoryGroups()
    {
        $service = $this->getAppRegistryService();

        $categoryGroups = $service->getCategoryGroups();

        foreach ($categoryGroups as $catGroup) {
            $this->assertArrayHasKey('name', $catGroup);
            $this->assertIsArray($categoryGroups['categories']);
        }
    }

    public function testUserHasDrafts()
    {
        $service = $this->getAppRegistryService();
        $userHasDrafts = $service->checkUserHasDraftApps();
        $this->assertFalse($userHasDrafts['has_drafts']);
    }

    public function testReviewFields()
    {
        $this->appRegistryServiceHelper->createRandomApplication(1, array('review_count' => 10, 'can_review' => true));
        $app = $this->getAppRegistryService()->getApplication(1);
        $this->assertEquals(10, $app->getReviewCount());
        $this->assertTrue($app->getCanReview());
    }

}
