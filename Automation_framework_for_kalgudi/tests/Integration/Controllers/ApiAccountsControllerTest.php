<?php

namespace Integration\Controllers;

use Interspire_Request;
use Interspire_Response;
use ApiAccountsController;
use Repository\Users;
use Store_Config;
use Store_User;

class ApiAccountsControllerTest extends \Interspire_IntegrationTest
{

    protected $originalConfigs = array();
    protected $currentUserId =  0;

    public function getCurrentUserId()
    {
        return $this->currentUserId;
    }

    public function setUp()
    {
        $this->currentUserId = 0;
        $preserveList = array(
            'StoreHash',
            'OAuth_ClientId',
            'OAuth_ClientSecret',
            'Feature_OAuthLogin',
        );
        foreach ($preserveList as $preserveKey) {
            $this->originalConfigs[$preserveKey] = Store_Config::get($preserveKey);
        }
        \Store::getStoreDb()->StartTransaction();
    }

    public function tearDown()
    {
        \Store::getStoreDb()->RollbackTransaction();

        // reload config after all the munging
        foreach ($this->originalConfigs as $key => $value) {
            Store_Config::override($key, $value);
        }

    }

    public function createRepository()
    {
        return new Users();
    }

    /**
     * @param Interspire_Request $request
     * @param Interspire_Response $response
     * @return ApiAccountsController
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

        $that = $this;
        $controller = $this->getMock('\\ApiAccountsController', array('getCurrentUserId'), array($request, $response));
        $controller->expects($this->any())
            ->method('getCurrentUserId')
            ->withAnyParameters()
            ->will($this->returnCallback(function() use ($that) {
                return $that->getCurrentUserId();
            }));
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

        $repo->create(array('username' => 'bacon', 'usertoken' => 'xxx', 'api_only' => 1));
        $repo->create(array('username' => 'egg', 'usertoken' => 'xxx', 'api_only' => 1));

        // fire up
        $resp = $controller->indexAction();

        $this->assertNotEmpty($resp['users']);
        $this->assertEquals(2, $resp['users']->count());

    }

    /**
     * Test the index action with existing user that has userapi = 1
     */
    public function testIndexActionExistingUsers()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);

        // setup controller
        $controller = $this->createController();

        // set up some users
        $repo = $this->createRepository();

        $repo->create(array('username' => 'bacon', 'usertoken' => 'xxx', 'api_only' => 1));
        $repo->create(array('username' => 'egg', 'usertoken' => 'xxx', 'api_only' => 1));
        $repo->create(array('username' => 'sausage',  'useremail' => 'sausage@example.com' , 'usertoken' => 'xxx', 'userapi' => 1, 'permissions' => $repo->getAllPermissions()));
        $repo->create(array('username' => 'OJ', 'useremail' => 'OJ@example.com', 'usertoken' => 'xxx', 'permissions' => $repo->getAllPermissions()));

        // fire up
        $resp = $controller->indexAction();

        $this->assertNotEmpty($resp['users']);
        $this->assertEquals(3, $resp['users']->count());

    }


    public function testCreateActionPost()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);

        // setup controller
        $request = new Interspire_Request(null, array(
            'username' => 'new_bacon',
            'apitoken' => 'xxx',
        ), null, array_merge($_SERVER, array('REQUEST_METHOD' => 'POST')));
        $controller = $this->createController($request);

        // fire up
        $controller->createAction();

        // fire up
        $resp = $controller->indexAction();

        $this->assertNotEmpty($resp['users']);
        $this->assertEquals(1, $resp['users']->count());

    }

    public function testEditActionPost()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);

        // set up some users
        $repo = $this->createRepository();
        $userId = $repo->create(array('username' => 'bacon', 'usertoken' => 'xxx', 'api_only' => 1));

        // setup controller
        $request = new Interspire_Request(null, array(
            'userId' => $userId,
            'username' => 'updated_bacon',
            'apitoken' => 'xxx',
        ), null, array_merge($_SERVER, array('REQUEST_METHOD' => 'POST')));
        $controller = $this->createController($request);

        // fire up
        $controller->editAction();

        // fire up
        $resp = $controller->indexAction();

        $this->assertNotEmpty($resp['users']);
        $user = $resp['users']->current();
        $this->assertEquals('updated_bacon', $user['username']);
    }

    public function testDeleteAction()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);

        // set up some users
        $repo = $this->createRepository();
        $baconId = $repo->create(array('username' => 'bacon', 'usertoken' => 'xxx', 'api_only' => 1));
        $fooId = $repo->create(array('username' => 'foo', 'usertoken' => 'xxx', 'api_only' => 1));

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
        $this->assertEquals(0, $resp['users']->count());
    }

    public function testDeleteActionExistingUser()
    {
        // override config so we have a valid oauth client
        Store_Config::override('Feature_OAuthLogin', true);

        // set up some users
        $repo = $this->createRepository();
        $baconId = $repo->create(array('username' => 'bacon', 'useremail' => 'bacon@example.com', 'usertoken' => 'xxx', 'userapi' => 1, 'permissions' => $repo->getAllPermissions()));
        $fooId = $repo->create(array('username' => 'foo', 'usertoken' => 'xxx', 'api_only' => 1));

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
        $this->assertEquals(0, $resp['users']->count());

        $bacon = Store_User::find("username = 'bacon'")->first();
        $this->assertNotEmpty($bacon);
        $this->assertFalse((bool) $bacon->getUserApi());

    }

}
