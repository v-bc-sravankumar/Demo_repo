<?php

namespace Integration\Controllers;

use Interspire_Request;
use Interspire_Response;
use Services\Bigcommerce\Auth\OAuthSession;
use UsersController;
use Repository\Users;
use Store_Config;
use Integration\Services\Bigcommerce\Auth\AuthServiceHelper;

class UsersControllerTest extends \Interspire_IntegrationTest
{

    protected $originalConfigs = array();
    protected $currentUser =  array('id' => 0, 'email' => 'test@example.org', 'username' => 'test');

    /**
     * @var AuthServiceHelper
     */
    public $authServiceHelper = null;

    public function __construct()
    {
        $this->authServiceHelper = new AuthServiceHelper();
    }

    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    public function setUp()
    {
        $preserveList = array(
            'StoreHash',
            'OAuth_ClientId',
            'OAuth_ClientSecret',
            'Feature_OAuthLogin',
        );
        foreach ($preserveList as $preserveKey) {
            $this->originalConfigs[$preserveKey] = Store_Config::get($preserveKey);
        }
        Store_Config::override('StoreHash', 'xxx');
        \Store::getStoreDb()->StartTransaction();
    }

    public function tearDown()
    {
        \Store::getStoreDb()->RollbackTransaction();

        // reload config after all the munging
        foreach ($this->originalConfigs as $key => $value) {
            Store_Config::override($key, $value);
        }
        // reset the data source
        $this->authServiceHelper->resetDataStore();
    }

    public function createRepository()
    {
        $repository = new Users();
        $repository->setAuthService($this->authServiceHelper->createAuthService());
        return $repository;
    }

    /**
     * @param Interspire_Request $request
     * @param Interspire_Response $response
     * @return UsersController
     */
    public function createController(Interspire_Request $request = null, Interspire_Response $response = null)
    {
        if ($request == null) {
            $request = new Interspire_Request();
        }

        if ($response == null) {
            $response = new Interspire_Response();
        }

        $response->setRequest($request);
        $request->setResponse($response);

        $oauthSession = new OAuthSession(array(
            'user' => $this->getCurrentUser(),
            'scope' => 'test',
            'access_token' => uniqid(),
        ));

        $that = $this;
        $controller = $this->getMock('\\UsersController', array('createAuthService', 'hasPermissionFor'), array($oauthSession));
        $controller->expects($this->any())
            ->method('createAuthService')
            ->withAnyParameters()
            ->will($this->returnCallback(function() use ($that) {
                return $that->authServiceHelper->createAuthService();
            }));

        $controller->expects($this->any())
            ->method('hasPermissionFor')
            ->withAnyParameters()
            ->will($this->returnValue(true));

        $controller->setRequest($request);
        $controller->setResponse($response);
        return $controller;
    }

    /**
     * Test the index action
     */
    public function testIndexAction()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);

        // setup controller
        $controller = $this->createController();

        // set up some users
        $repo = $this->createRepository();

        $repo->create(array('username' => 'bacon+testindex@example.com', 'useremail' => 'bacon+testindex@example.com', 'permissions' => array(AUTH_Manage_Users)));
        $repo->create(array('username' => 'foo+testindex@example.com', 'useremail' => 'foo+testindex@example.com', 'permissions' => array(AUTH_Manage_Users)));

        // fire up
        $resp = $controller->indexAction();

        $this->assertNotEmpty($resp['users']);
        $this->assertEquals(3, $resp['users']->count()); // 3 users including admin

    }


    public function testCreateActionPost()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);

        // setup controller
        $request = new Interspire_Request(null, array(
            'useremail' => 'test+testpost@example.com',
            'permissions' => array(
                'admin' => array(
                    AUTH_Manage_Users,
                    AUTH_Add_User,
                    AUTH_Edit_Users,
                    AUTH_Delete_Users,
                ),
            ),
        ), null, array_merge($_SERVER, array('REQUEST_METHOD' => 'POST')));
        $controller = $this->createController($request);

        // fire up
        $controller->createAction();

        $user = \Store_User::find("useremail = 'test+testpost@example.com'")->first();
        $this->assertNotEmpty($user);
        $this->assertEquals('test+testpost@example.com', $user->getUserEmail());

    }

    public function testEditActionPost()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);

        // set up some users
        $repo = $this->createRepository();
        $userId = $repo->create(array('uid' => 1, 'username' => 'bacon+testedit@example.org', 'useremail' => 'bacon+testedit@example.org', 'permissions' => array(
                AUTH_Manage_Users,
                AUTH_Add_User,
                AUTH_Edit_Users,
                AUTH_Delete_Users,
            ),
        ));

        // setup controller
        $request = new Interspire_Request(null, array(
            'userId' => $userId,
            'useremail' => 'test-changed@example.com',
            'permissions' => array(
                'admin' => array(
                    AUTH_Manage_Users,
                    AUTH_Add_User,
                ),
            ),
        ), null, array_merge($_SERVER, array('REQUEST_METHOD' => 'POST')));
        $controller = $this->createController($request);

        // fire up
        $controller->editAction();

        /* @var \Store_User $user */
        $user = \Store_User::find($userId)->first();

        // should not change email
        $this->assertNotEquals('test-changed@example.com', $user->getUserEmail());
        $this->assertEquals('bacon+testedit@example.org', $user->getUserEmail());

        $userPermissions = $repo->getPermissionsForUser($user->getId());

        $this->assertNotContains(AUTH_Edit_Users, $userPermissions);
        $this->assertNotContains(AUTH_Delete_Users, $userPermissions);

    }

    public function testEditActionPostTransferOwnership()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);

        // set up some users
        $repo = $this->createRepository();
        $userId = $repo->create(array('uid' => 1, 'username' => 'bacon+testedit@example.org', 'useremail' => 'bacon+testedit@example.org', 'permissions' => array(
                AUTH_Manage_Users,
                AUTH_Add_User,
                AUTH_Edit_Users,
                AUTH_Delete_Users,
            ),
        ));

        // setup controller
        $request = new Interspire_Request(null, array(
            'userId' => $userId,
            'userrole' => 'owner',
            'permissions' => array(
                'admin' => array(
                    AUTH_Manage_Users,
                    AUTH_Add_User,
                ),
            ),
        ), null, array_merge($_SERVER, array('REQUEST_METHOD' => 'POST')));
        $controller = $this->createController($request);

        // fire up
        $controller->editAction();

        /* @var \Store_User $user */
        $user = \Store_User::find(1)->first();
        $this->assertEquals('bacon+testedit@example.org', $user->getUserEmail());


    }

    /**
     * Some store users in production has their email address as username after ownership transfer bug
     * this test made sure that ownership transfer will work on those anomalies.
     */
    public function testEditActionPostTransferOwnershipEmailUsername()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);

        $adminUser = \Store_User::find(1)->first();
        $adminUser->setUsername($adminUser->getUserEmail());
        $adminUser->save();

        // set up some users
        $repo = $this->createRepository();
        $userId = $repo->create(array('uid' => 1, 'username' => 'bacon+testedit@example.org', 'useremail' => 'bacon+testedit@example.org', 'permissions' => array(
                AUTH_Manage_Users,
                AUTH_Add_User,
                AUTH_Edit_Users,
                AUTH_Delete_Users,
            ),
        ));

        // setup controller
        $request = new Interspire_Request(null, array(
            'userId' => $userId,
            'userrole' => 'owner',
            'permissions' => array(
                'admin' => array(
                    AUTH_Manage_Users,
                    AUTH_Add_User,
                ),
            ),
        ), null, array_merge($_SERVER, array('REQUEST_METHOD' => 'POST')));
        $controller = $this->createController($request);

        // fire up
        $controller->editAction();

        /* @var \Store_User $user */
        $user = \Store_User::find(1)->first();
        $this->assertEquals('bacon+testedit@example.org', $user->getUserEmail());


    }

    public function testDeleteAction()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);

        // set up some users
        $repo = $this->createRepository();
        $baconId = $repo->create(array('uid' => 1, 'useremail' => 'bacon+testdelete@example.com', 'username' => 'bacon+testdelete@example.com', 'permissions' => array(AUTH_Manage_Users)));
        $fooId = $repo->create(array('uid' => 2, 'useremail' => 'foo+testdelete@example.com', 'username' => 'foo+testdelete@example.com', 'permissions' => array(AUTH_Manage_Users)));

        // setup controller
        $request = new Interspire_Request(null, array(
            'users' => array($baconId, $fooId),
        ), null, array_merge($_SERVER, array('REQUEST_METHOD' => 'POST')));
        $controller = $this->createController($request);

        // fire up
        $controller->deleteAction();

        // fire up
        $resp = $controller->indexAction();

        $this->assertNotEmpty($resp['users']);
        $this->assertEquals(1, $resp['users']->count()); // 1 with admin user
    }

}
