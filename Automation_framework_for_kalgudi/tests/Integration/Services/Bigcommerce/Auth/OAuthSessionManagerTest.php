<?php

namespace Integration\Services\Bigcommerce\Auth;

use Services\Bigcommerce\Auth\OAuthSession;
use Services\Bigcommerce\Auth\OAuthSessionManager;

class OAuthSessionManagerTest extends \Interspire_IntegrationTest
{

    protected function generateOAuthSession()
    {
        $id = (int) microtime(true);
        return new OAuthSession(array(
            'user' => array(
                'id' => $id,
                'username' => 'username_'.$id,
                'email' => 'user_'.$id.'@example.com',
                'scopes' => array('test1', 'test2'),
            ),
            'scopes' => 'test1 test2',
            'access_token' => uniqid(),
        ));
    }


    public function testGetCurrentSession()
    {
        $sessionManager = OAuthSessionManager::getInstance();
        $session = $this->generateOAuthSession();
        $sessionManager->createSession($session);

        $currentSession = $sessionManager->getCurrentSession();
        $this->assertEquals($session->getAccessToken(), $currentSession->getAccessToken());
        $this->assertEquals($session->getScopes(), $currentSession->getScopes());

    }

    public function testDestroyCurrentSession()
    {
        $sessionManager = OAuthSessionManager::getInstance();
        $session = $this->generateOAuthSession();
        $sessionManager->createSession($session);

        $currentSession = $sessionManager->getCurrentSession();
        $this->assertEquals($session->getAccessToken(), $currentSession->getAccessToken());
        $this->assertEquals($session->getScopes(), $currentSession->getScopes());

        $sessionManager->destroyCurrentSession();
        $this->assertNull($sessionManager->getCurrentSession());

    }

} 
