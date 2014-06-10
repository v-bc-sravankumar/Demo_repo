<?php

namespace Integration\Services\Bigcommerce\AppRegistry;

use Config\Environment;
use PHPUnit_Framework_TestCase;
use Platform\Account;
use Services\Bigcommerce\AppRegistry\Entity\Application;
use Services\Bigcommerce\AppRegistry\Entity\Install;

class AppRegistryServiceHelper extends PHPUnit_Framework_TestCase
{

    public $dataStore = array();

    /**
     * @param $path
     * @param $query
     * @return \Interspire_Http_Response
     * @throws \Interspire_Http_ClientError
     */
    public function appRegistryServiceGet($path, $query)
    {
        if (preg_match('#^/stores/([^/]+)/apps$#', $path, $matches)) {
            $apps = $this->dataStore['apps'];
            if (! empty($query['installed'])) {
                $installedAppIds = array_reduce($this->dataStore['installs'], function($appIds, $install) {
                    if ('installed' === $install['status']) {
                        $appIds[] = $install['application_id'];
                    }

                    return $appIds;
                }, array());
                $apps = array_filter($this->dataStore['apps'], function($app) use ($installedAppIds) {
                    return in_array($app['id'], $installedAppIds);
                });
            }

            return new \Interspire_Http_Response(200, array(),
                json_encode(array('apps' => $apps)));
        } else if (preg_match('#^/stores/([^/]+)/apps/([^/]+)$#', $path, $matches)) {
            if ($this->dataStore['apps'][$matches[2]]) {
                return new \Interspire_Http_Response(200, array(),
                    json_encode($this->dataStore['apps'][$matches[2]]));
            }
        } else if (preg_match('#^/stores/([^/]+)/apps/([^/]+)/reviews$#', $path, $matches)) {
            return new \Interspire_Http_Response(200, array(),
                json_encode(array('reviews' => $this->dataStore['reviews'])));
        } else if (preg_match('#^/stores/([^/]+)/apps/([^/]+)/reviews/([0-9]+)$#', $path, $matches)) {
            return new \Interspire_Http_Response(200, array(),
                json_encode($this->dataStore['reviews'][$matches[3]]));
        } else if (preg_match('#^/users/[0-9]+/apps/category_groups$#', $path, $matches)) {
            return new \Interspire_Http_Response(200, array(),
                json_encode($this->dataStore['category_groups']));
        } else if (preg_match('#^/stores/([^/]+)/installs$#', $path, $matches)) {

            $storeHash = urldecode($matches[1]);
            $installs = array_filter($this->dataStore['installs'], function($install) use ($storeHash) {
                return $install['store_hash'] == $storeHash;
            });
            return new \Interspire_Http_Response(200, array(),
                json_encode(array('installs' => $installs)));

        } else if (preg_match('#^/stores/([^/]+)/installs/([0-9]+)#', $path, $matches)) {

            $storeHash = urldecode($matches[1]);
            $installId = $matches[2];
            $installs = array_filter($this->dataStore['installs'], function($install) use ($storeHash, $installId) {
                return $install['store_hash'] == $storeHash
                    && $install['id'] == $installId;
            });

            if (empty($installs)) {
                throw new \Interspire_Http_ClientError(json_encode(array('error' => 'Installation not found')), 404);
            }

            return new \Interspire_Http_Response(200, array(),
                json_encode(current($installs)));

        } else if (preg_match('#^/stores/([^/]+)/apps/([^/]+)/install#', $path, $matches)) {

            $storeHash = urldecode($matches[1]);
            $applicationId = urldecode($matches[2]);
            $userId = $query['user_id'];

            /* @var Application $application */
            $application = $this->dataStore['apps'][$applicationId];

            $scopes = implode(' ', array_map(function($scope) use ($storeHash) {
                return str_replace('{store_hash}', $storeHash, $scope);
            }, $application['scopes']));

            $install = $this->createRandomInstall($applicationId, $userId);

            $loadUrl = Environment::get('services.auth.url');
            $loadUrl .= '/oauth2/authorize?';
            $loadUrl .= '&context=stores/'.urlencode($storeHash);
            $loadUrl .= '&scope='.$scopes;
            $loadUrl .= '&client_id='.$application['client_id'];
            $loadUrl .= '&redirect_uri='.urlencode($application['callback_url']);

            return new \Interspire_Http_Response(200, array(),
                json_encode(array(
                    'install' => $install,
                    'auth_url' => $loadUrl,
                )));

        } else if (preg_match('#^/stores/([^/]+)/apps/([^/]+)/load#', $path, $matches) !== false) {

            $storeHash = urldecode($matches[1]);
            $applicationId = urldecode($matches[2]);
            $userId = $query['user_id'];
            $deepLinkId = isset($query['deep_link']) ? $query['deep_link'] : false;

            /* @var Application $application */
            $application = $this->dataStore['apps'][$applicationId];
            $installs = array_filter($this->dataStore['installs'], function($install) use ($storeHash, $applicationId, $userId) {
                return $install['store_hash'] == $storeHash
                    && $install['application_id'] == $applicationId
                    && $install['user_id'] == $userId;
            });

            if (empty($installs)) {
                return new \Interspire_Http_Response(200, array(),
                    json_encode(array(
                        'authorized' => false,
                    )));
            }

            $url = $application['load_url'];
            if ($deepLinkId) {
                $url = $this->dataStore['deep_links'][$deepLinkId]['url'];
            }

            return new \Interspire_Http_Response(200, array(),
                json_encode(array(
                    'authorized' => true,
                    'url' => $url,
                )));

        }

        throw new \Interspire_Http_ClientError('No matching route for [GET] '.$path, 404);

    }

    /**
     * Post handler for app registry
     *
     * @param $path
     * @param $payload
     * @return \Interspire_Http_Response
     * @throws \Interspire_Http_ClientError
     */
    public function appRegistryServicePost($path, $payload)
    {
        if (preg_match('#^/stores/([^/]+)/apps/([^/]+)/uninstall#', $path, $matches)) {

            $storeHash = urldecode($matches[1]);
            $applicationId = urldecode($matches[2]);
            $userId = $payload['user_id'];

            $installs = array_filter($this->dataStore['installs'], function($install) use ($storeHash, $applicationId, $userId) {
                return $install['store_hash'] == $storeHash
                && $install['application_id'] == $applicationId
                && $install['user_id'] == $userId;
            });

            if (empty($installs)) {
                return new \Interspire_Http_Response(404, array(),
                    json_encode(array(
                        'error' => 'Installation not found.',
                    )));
            }

            return new \Interspire_Http_Response(200, array(),
                json_encode(array(
                    'install' => current($installs),
                )));

        } else if (preg_match('#^/stores/([^/]+)/apps/([^/]+)/reviews$#', $path, $matches)) {
            $payload = json_decode($payload, true);
            $id = count($this->dataStore['reviews']) + 1;
            $review = array(
                'application_id' => $matches[2],
                'id' => $id,
                'rating' => $payload['rating'],
                'subject' => $payload['subject'],
                'content' => $payload['content'],
                'website' => $payload['website'],
                'name' => 'Test Review',
                'created_at' => time(),
                'updated_at' => time()+1
            );

            $this->dataStore['reviews'][$id] = $review;

            return new \Interspire_Http_Response(200, array(), json_encode($review));
        }

        throw new \Interspire_Http_ClientError('No matching route for [POST] '.$path, 404);

    }

    /**
     * Put handler for app registry
     *
     * @param $path
     * @param $payload
     * @return \Interspire_Http_Response
     * @throws \Interspire_Http_ClientError
     */
    public function appRegistryServicePut($path, $payload)
    {
        if (preg_match('#^/stores/([^/]+)/apps/([^/]+)/reviews/([0-9]+)$#', $path, $matches)) {
            $review = $this->dataStore['reviews'][$matches[3]];
            $payload = json_decode($payload, true);
            $review = array_merge($review, $payload);
            return new \Interspire_Http_Response(200, array(),
                json_encode($review));
        }

        throw new \Interspire_Http_ClientError('No matching route for [POST] '.$path, 404);
    }

    /**
     * @return \Services\Bigcommerce\AppRegistry\AppRegistryService
     */
    public function createAppRegistryService()
    {
        $response = null;
        $that = $this;
        $httpClient = $this->getMock('\Interspire_Http_Client', array('post', 'put', 'get', 'getResponse'));

        // mock get
        $httpClient->expects($this->any())->method('get')
            ->withAnyParameters()
            ->will($this->returnCallback(function ($uri, $query = null) use (&$response, $httpClient, $that) {
                $parts = parse_url($uri);
                $response = $that->appRegistryServiceGet($parts['path'], (isset($parts['query']) ? $parts['query'] : $query));
                return $httpClient;
            }));

        // mock post
        $httpClient->expects($this->any())->method('post')
            ->withAnyParameters()
            ->will($this->returnCallback(function ($uri, $payload = null) use (&$response, $httpClient, $that) {
                $parts = parse_url($uri);
                $response = $that->appRegistryServicePost($parts['path'], $payload);
                return $httpClient;
            }));

        // mock put
        $httpClient->expects($this->any())->method('put')
            ->withAnyParameters()
            ->will($this->returnCallback(function ($uri, $payload = null) use (&$response, $httpClient, $that) {
                $parts = parse_url($uri);
                $response = $that->appRegistryServicePut($parts['path'], $payload);
                return $httpClient;
            }));

        $httpClient->expects($this->any())->method('getResponse')
            ->withAnyParameters()
            ->will($this->returnCallback(function () use (&$response) {
                return $response;
            }));

        $appRegistryService = $this->getMock('\Services\Bigcommerce\AppRegistry\AppRegistryService', array('getHttpClient'));
        $appRegistryService->expects($this->any())
            ->method('getHttpClient')
            ->withAnyParameters()
            ->will($this->returnValue($httpClient));
        return $appRegistryService;
    }

    public function resetDataStore()
    {
        $this->dataStore = array(
            'apps' => array(),
            'installs' => array(),
            'deep_links' => array(),
            'screenshots' => array(),
            'reviews' => array(),
            'category_groups' => array(
                array(
                    'name' => 'Group 1',
                    'categories' => array(
                        array(
                            'id' => 1,
                            'name' => 'Category 1'
                        ),
                        array(
                            'id' => 2,
                            'name' => 'Category 2'
                        )
                    )
                ),
                array(
                    'name' => 'Group 2',
                    'categories' => array(
                        array(
                            'id' => 3,
                            'name' => 'Category 3'
                        ),
                        array(
                            'id' => 4,
                            'name' => 'Category 4'
                        )
                    )
                )
            )
        );
    }

    /**
     * Create a new random application in the internal dataStore
     *
     * @param int $id
     * @param array $values
     * @return array
     */
    public function createRandomApplication($id = null, $values = array())
    {
        if (empty($id)) {
            $id = count($this->dataStore['apps'])+1;
        }
        $appDomain = 'example-application-'.$id.'.org';
        $appUrl = 'https://www.'.$appDomain;

        $application = array(
            'id' => $id,
            'name' => 'Test Application #'.$id,
            'description' => 'Test application #'.$id.' description ',
            'system_description' => 'Test application #'.$id.' system description',
            'logo_url' => 'logo.png',
            'full_logo_url' => $appUrl.'/logo.png',
            'status' => 'sandbox',
            'version' => '1.0',
            'license' => 'MIT',
            'support_url' => $appUrl.'/support',
            'support_email' => 'support@'.$appDomain,
            'languages' => 'en_us en_au',
            'rating' => 5,
            'client_id' => uniqid(),
            'client_secret' => uniqid(),
            'scopes' =>
            array(
                'store_{store_hash}_v2_products',
                'store_{store_hash}_v2_orders',
            ),
            'callback_url' => $appUrl.'/callback',
            'developer_id' => 1,
            'menu_icon_url' => 'menu_icon.png',
            'full_menu_icon_url' => $appUrl.'/menu_icon.png',
            'menu_logo' => $appUrl.'/menu_logo.png',
            'app_type' => 'active',
            'load_url' => $appUrl.'/load',
            'created_at' => time(),
            'updated_at' => time()+1,
            'countries' => array(
                'AU', 'US'
            ),
            'deep_links' => array(
                $this->createRandomDeepLink($id),
                $this->createRandomDeepLink($id),
            ),
            'screenshots' => array(
                $this->createRandomScreenshot($id),
                $this->createRandomScreenshot($id),
            ),
            'pricing' => 'Free',
            'pricing_type' => 'free',
            'enable_free_trial' => false,
            'free_trial_days' => 0,
            'categories' => array(
                $this->createRandomCategory(),
                $this->createRandomCategory()
            ),
            'scripts' => array(
                $this->createRandomScript($id),
                $this->createRandomScript($id),
            ),
            'summary' => 'Summary Of App',
            'featured' => true,
            'featured_order' => $id,
            'approved_at' => time(),
            'legacy_name' => null,
            'legacy' => false,
            'review_count' => 0,
            'can_review' => false,
        );
        $application = array_merge($application, $values);
        $this->dataStore['apps'][$id] = $application;
        return $application;
    }

    public function createRandomScript($appId)
    {
        $id = count($this->dataStore['scripts'])+1;
        $appDomain = 'example-application-'.$appId.'.org';
        $appUrl = 'https://www.'.$appDomain;

        $script = array(
            'id' => $id,
            'application_id' => $appId,
            'url' => $appUrl.'/scripts/'.$id.'.js',
            'created_at' => time(),
            'updated_at' => time()+1,
        );

        $this->dataStore['scripts'][$id] = $script;
        return $script;
    }

    /**
     * Create a new random deep link for the given appId
     *
     * @param $appId
     * @return array
     */
    public function createRandomDeepLink($appId)
    {
        $id = count($this->dataStore['deep_links'])+1;
        $appDomain = 'example-application-'.$appId.'.org';
        $appUrl = 'https://www.'.$appDomain;
        $deepLink = array(
            'id' => $id,
            'name' => 'Deep link #'.$id,
            'application_id' => $appId,
            'url' => $appUrl.'/deep_links/1',
            'created_at' => time(),
            'updated_at' => time()+1,
        );
        $this->dataStore['deep_links'][$id] = $deepLink;
        return $deepLink;
    }

    /**
     * Create a new random screenshot for the given appId
     *
     * @param $appId
     * @return array
     */
    public function createRandomScreenshot($appId)
    {
        $id = count($this->dataStore['screenshots'])+1;
        $appDomain = 'example-application-'.$appId.'.org';
        $appUrl = 'https://www.'.$appDomain;
        $screenshot = array(
            'id' => $id,
            'alt_text' => 'Screenshot #'.$id,
            'application_id' => $appId,
            'url' => 'screenshot/1',
            'full_url' => $appUrl.'/screenshot/1',
            'created_at' => time(),
            'updated_at' => time()+1,
        );
        $this->dataStore['screenshots'][$id] = $screenshot;
        return $screenshot;
    }

    /**
     * Create a random Category
     *
     * @return array
     */
    public function createRandomCategory()
    {
        $id = count($this->dataStore['categories'])+1;
        $category = array(
            'id' => $id,
            'name' => "Category $id"
        );
        $this->dataStore['categories'][$id] = $category;

        return $category;
    }

    /**
     * Create a random Review
     *
     * @param $appId
     *
     * @return array
     */
    public function createRandomReview($appId)
    {
        $id = count($this->dataStore['reviews']) + 1;
        $review = array(
            'application_id' => $appId,
            'id' => $id,
            'rating' => rand(1, 5),
            'subject' => "Subject $id",
            'content' => "Content $id",
            'name' => "Name $id",
            'website' => "http://www.test.com/$id",
            'created_at' => time(),
            'updated_at' => time()+1
        );

        $this->dataStore['reviews'][$id] = $review;

        return $review;
    }

    /**
     * Create a new random install for the given appId
     *
     * @param $appId
     * @param $userId
     * @param string $status
     * @return array
     */
    public function createRandomInstall($appId, $userId, $status = 'installing')
    {
        $app = $this->dataStore['apps'][$appId];
        $id = count($this->dataStore['installs']) + 1;
        $install = array (
            'id' => $id,
            'application_id' => $appId,
            'store_hash' => Account::getInstance()->getStoreHash(),
            'user_id' => $userId,
            'status' => $status,
            'version' => $app['version'],
            'created_at' => time(),
            'updated_at' => time()+1,
        );
        $this->dataStore['installs'][$id] = $install;
        return $install;
    }

    public function testCreateAppRegistryService()
    {
        $this->assertNotEmpty($this->createAppRegistryService());
    }
}
