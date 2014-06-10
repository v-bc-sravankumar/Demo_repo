<?php

namespace Integration\Services\Bigcommerce\Auth;

use Repository\Users;
use Services\Bigcommerce\Auth\MigrationHelper;
use Services\Bigcommerce\Auth\OAuthSession;
use Store_Config;
use Store_User;

class MigrationHelperTest extends \Interspire_IntegrationTest
{

	/**
	 * @var AuthServiceHelper
	 */
	protected $authServiceHelper = null;

	public function __construct()
	{
		$this->authServiceHelper = new AuthServiceHelper();
	}

	public function setUp()
	{
		// reset the data source
		$this->authServiceHelper->resetDataStore();
		\Store::getStoreDb()->StartTransaction();

		// why does modern ui even default to false on new stores these days?
		\Store_Feature::enable('ModernUI');
	}

	public function tearDown()
	{
		\Store::getStoreDb()->RollbackTransaction();
		// reset the data source
		$this->authServiceHelper->resetDataStore();

		// restore ModernUI value
		\Store_Feature::disable('ModernUI');
		// reset the config values set by MigrationHelper
		Store_Config::cancelAll();
		Store_Config::schedule('OAuth_ClientId', Store_Config::getDefault('OAuth_ClientId'));
		Store_Config::schedule('OAuth_ClientSecret', Store_Config::getDefault('OAuth_ClientSecret'));
		Store_Config::schedule('Feature_OAuthLoginSkipMigrationChecks', Store_Config::getDefault('Feature_OAuthLoginSkipMigrationChecks'));
		Store_Config::commit();
		unset($_SESSION['OAUTH_SESSION']);
	}

	/**
	 * @param $email
	 * @param $password
	 * @return MigrationHelper
	 */
	public function createMigrationHelper($email, $password)
	{
		$migrationHelper = $this->getMock('\Services\Bigcommerce\Auth\MigrationHelper',
			array('createAuthService'));
		$migrationHelper->expects($this->any())
			->method('createAuthService')
			->withAnyParameters()
			->will($this->returnValue($this->authServiceHelper->createAuthService()));
		return $migrationHelper;
	}

	public function testBootstrapShouldCreateSession()
	{
		$migrationHelper = $this->createMigrationHelper('test@example.com', 'password');
		$resp = $migrationHelper->bootstrap();
		$this->assertTrue($resp);
		$session = OAuthSession::getCurrentSession();
		$this->assertNotNull($session);
		$this->assertEquals('test@example.com', $session->getUser()->getEmail());
	}

	public function testBootstrapWithUsers()
	{
		$repo = new Users();
		$repo->create(array(
			'useremail' => 'test1@example.org',
			'username' => 'test1',
			'permissions' => $repo->getAllPermissions(),
		));

		$repo->create(array(
			'useremail' => 'test2@example.org',
			'username' => 'test2',
			'permissions' => $repo->getAllPermissions(),
		));

		$this->authServiceHelper->dataStore['users'][] = array(
			'id' => 9001,
			'email' => 'test1@example.org',
		);

		$this->authServiceHelper->dataStore['users'][] = array(
			'id' => 9002,
			'email' => 'test2@example.org',
		);

		$migrationHelper = $this->createMigrationHelper('test@example.com', 'password');
		$resp = $migrationHelper->bootstrap();
		$this->assertTrue($resp);
		$session = OAuthSession::getCurrentSession();
		$this->assertNotNull($session);
		$this->assertEquals('test@example.com', $session->getUser()->getEmail());

		$test1 = Store_User::find("useremail = 'test1@example.org'")->first();
		$this->assertEquals(9001, $test1->getUid());

		$test2 = Store_User::find("useremail = 'test2@example.org'")->first();
		$this->assertEquals(9002, $test2->getUid());

	}

    public function testBootstrapWithUsersSkipMigrationChecksAndNonOwner()
    {

        \Store_Feature::enable('OAuthLoginSkipMigrationChecks', true);

        $repo = new Users();
        $repo->create(array(
            'useremail' => 'test1@example.org',
            'username' => 'test1',
            'permissions' => $repo->getAllPermissions(),
        ));

        $repo->create(array(
            'useremail' => 'test2@example.org',
            'username' => 'test2',
            'permissions' => $repo->getAllPermissions(),
        ));

        $this->authServiceHelper->dataStore['users'][] = array(
            'id' => 9001,
            'email' => 'test1@example.org',
        );

        $this->authServiceHelper->dataStore['users'][] = array(
            'id' => 9002,
            'email' => 'test2@example.org',
        );

        $migrationHelper = $this->createMigrationHelper('test1@example.org', 'password');
        $resp = $migrationHelper->bootstrap();
        $this->assertTrue($resp);
        $session = OAuthSession::getCurrentSession();
        $this->assertNotNull($session);
        $this->assertEquals('test@example.com', $session->getUser()->getEmail());

        $owner = Store_User::find(1)->first();
        $this->assertNotEmpty($owner->getUid());

        $test1 = Store_User::find("useremail = 'test1@example.org'")->first();
        $this->assertEquals(9001, $test1->getUid());

        $test2 = Store_User::find("useremail = 'test2@example.org'")->first();
        $this->assertEquals(9002, $test2->getUid());

    }

}
