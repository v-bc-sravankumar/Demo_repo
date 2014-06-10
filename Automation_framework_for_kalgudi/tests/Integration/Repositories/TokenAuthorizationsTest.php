<?php

use Services\Auth\Client\TokenAuthorization;
use Services\Auth\Client\AccessToken;
use Services\Auth\Client\ResourceOwner;

use Repository\TokenAuthorizations;

class Integration_Repositories_TokenAuthorizationsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenAuthorizations
     */
    protected $tokenAuthorizations;

    const TEST_SERVER = 'TestServer';

    public function setUp()
    {
        $this->tokenAuthorizations = new TokenAuthorizations();
    }

    public function testRemoveDeletesChildRecords()
    {
        $serverName = self::TEST_SERVER;
        $accessToken = new AccessToken('y3hOKweDLqtRv7hGxkH29I53UhZ0PVpEv3QfQYiureigvHUSIAM-KQ',
            '6tYeqzNSIgWVk7BR2U-9rvdClck');
        $resourceOwner = new ResourceOwner('test@example.com');
        $grantedAuthorities = array(
            'TEST_1',
            'TEST_2',
        );
        $tokenAuthorization = new TokenAuthorization($accessToken, $serverName, $resourceOwner, $grantedAuthorities);

        $this->tokenAuthorizations->save($tokenAuthorization);

        $id = $tokenAuthorization->getId();

        $this->tokenAuthorizations->remove($tokenAuthorization);

        $select = new \DataModel_SelectQuery("SELECT * FROM [|PREFIX|]token_authorities");
        $select->whereEquals('token_authorization_id', $id);

        $this->assertEquals(0, $select->countResult());
    }

    public function testRemoveAllForServerDeletesChildRecords()
    {
        $serverName = self::TEST_SERVER;
        $accessToken1 = new AccessToken('y3hOKweDLqtRv7hGxkH29I53UhZ0PVpEv3QfQYiureigvHUSIAM-KQ',
            '6tYeqzNSIgWVk7BR2U-9rvdClck');
        $accessToken2 = new AccessToken('y3hOKweDLqtRv7hGxkH29I53UhZ0PVpEv3QfQYiureigvHUSIAM-KR',
            '6tYeqzNSIgWVk7BR2U-9rvdClcl');
        $resourceOwner1 = new ResourceOwner('test1@example.com');
        $resourceOwner2 = new ResourceOwner('test2@example.com');
        $grantedAuthorities = array(
            'TEST_1',
            'TEST_2',
        );

        $tokenAuthorization1 = new TokenAuthorization($accessToken1, $serverName, $resourceOwner1, $grantedAuthorities);
        $tokenAuthorization2 = new TokenAuthorization($accessToken2, $serverName, $resourceOwner2, $grantedAuthorities);
        $tokenAuthorization2->setTestMode(true);

        $this->tokenAuthorizations->save($tokenAuthorization1);
        $this->tokenAuthorizations->save($tokenAuthorization2);

        $ids = array(
            $tokenAuthorization1->getId(),
            $tokenAuthorization2->getId(),
        );

        $this->tokenAuthorizations->removeAllForServer($serverName);

        $select = new \DataModel_SelectQuery("SELECT * FROM [|PREFIX|]token_authorities");
        $select->where('token_authorization_id', 'IN', $ids);

        $this->assertEquals(0, $select->countResult());
    }

    public function tearDown()
    {
        $this->tokenAuthorizations->removeAllForServer(self::TEST_SERVER);
    }

}