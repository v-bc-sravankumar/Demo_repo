<?php
use Store\Users;

/**
 * This test suite is disabled temporarily - to be fixed in the next release
 */
class Integration_Store_UsersTest extends Interspire_IntegrationTest
{
    public function testGetLoggedInUser()
    {
        $this->login('admin');
        $user = Store_User::getLoggedInUser();
        $this->assertNotNull($user);
    }

    public function testGetApiDetailsDisabledUser()
    {
        $this->login('admin');
        $user = Store_User::findByUserRole('admin')->first();
        $this->setMockPermissionValidator($user, true);

        if ($user->getUserApi()) {
            $user->setUserApi(0);
            $user->save();
        }

        $this->assertNull($user->getApiDetails(false));
        $this->assertNotNull($user->getApiDetails(true));
    }

    public function testGetApiDetailsEnabledUser()
    {
        $this->login('admin');
        $user = Store_User::findByUserRole('admin')->first();
        $this->setMockPermissionValidator($user, true);

        if (!$user->getUserApi()) {
            $user->setUserApi(1);
            $user->save();
        }

        $this->assertNotNull($user->getApiDetails(true));
    }

    public function testGetApiDetailsUserToken()
    {
        $this->login('admin');
        $user = Store_User::findByUserRole('admin')->first();
        $this->setMockPermissionValidator($user, true);

        if (!$user->getUserApi()) {
            $user->setUserApi(1);
            $user->save();
        }

        $api = $user->getApiDetails();
        $this->assertEquals($user->getUserToken(), $api->token);
    }

    public function testGetApiDetailsEnableForUser()
    {
        $this->login('admin');
        $user = Store_User::findByUserRole('admin')->first();
        $this->setMockPermissionValidator($user, true);

        if ($user->getUserApi()) {
            $user->setUserApi(0); //make sure its disabled
            $user->save();
        }

        $api = $user->getApiDetails();
        $this->assertEquals($user->getUserToken(), $api->token);
        $this->assertEquals(1, $user->getUserApi());
    }

    public function testGetApiDetailsNoUser()
    {
        $user = new Store_User();
        $this->setMockPermissionValidator($user, false);
        $this->assertNull($user->getApiDetails());
    }

    public function testGetApiDetailsNonAdminUserEnabled()
    {
        $user = $this->createUser('test1', md5('test1111'));
        $user->setUserApi(1); //make sure its enabled
        $user->save();

        $this->setMockPermissionValidator($user, false);
        $this->assertNull($user->getApiDetails());

        $user->delete();
    }

    public function testGetApiDetailsNonAdminUserDisabled()
    {
        $user = $this->createUser('test2', md5('test1111'));
        $this->setMockPermissionValidator($user, true);

        $this->assertNotNull($user->getApiDetails());

        $user->delete();
    }

    //Test helpers
    private function createUser($name, $password)
    {
        $user = new Store_User();

        $user->setUsername($name);
        $user->setUserPass($password);
        $user->setUserApi(0); //make sure its disabled
        $user->setUserToken(''); //user api token
        $user->setToken('cookietokenfor'.$name);
        $user->setUserEmail($name.'@'.$name.'.com');
        $user->save();

        return $user;
    }

    public function login($username)
    {
        parent::login($username, false);
    }

    private function setMockPermissionValidator($user, $expectedResult)
    {
        $mock = $this->getMock('ISC_ADMIN_AUTH', array('HasPermission'));
        $mock->expects($this->any())
            ->method('HasPermission')
            ->will($this->returnValue($expectedResult));

        $user->setPermissionValidator($mock);
    }

}

