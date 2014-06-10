<?php

namespace Integration\Services\Bigcommerce\Auth;

use PHPUnit_Framework_TestCase;
use Services\Bigcommerce\Auth\OAuthClient;

class AuthServiceHelper extends PHPUnit_Framework_TestCase
{

	public $dataStore = array();

	public function authServiceGet($path, $query)
	{
		if ($path == '/users') {

			$results = array('users' => array());
            $q = $query;
            if (is_string($query)) {
                parse_str($query, $q);
            }
			if (!empty($q['email'])) {
				$results['users'] = array_filter($this->dataStore['users'], function($user) use ($q) {
					return $user['email'] == $q['email'];
				});
			} else {
				$results['users'] = $this->dataStore['users'];
			}
			return new \Interspire_Http_Response(200, array(),
				json_encode($results));

		} else if (preg_match('/^\/users\/([0-9]+)$/', $path, $matches)) {
			$userId = $matches[1];
			return new \Interspire_Http_Response(200, array(),
				json_encode($this->dataStore['users'][$userId]));
		}
		return null;
	}

	public function authServicePost($path, $payload)
	{
		if ($path == '/users') {

			$users = json_decode($payload, true);
			$user = $users['user'];
			$user['id'] = count($this->dataStore['users']) + 1;
			$this->dataStore['users'][$user['id']] = $user;

			return new \Interspire_Http_Response(200, array(),
				json_encode($user));
		} else if ($path == '/scopes') {

			$scopes = json_decode($payload, true);
			$scope = $scopes['scopes'];
			$scope['id'] = count($this->dataStore['scopes']) + 1;
			$this->dataStore['scopes'][$scope['id']] = $scope;

			return new \Interspire_Http_Response(200, array(),
				json_encode($scopes));

		} else if ($path == '/bootstrap/store') {
			$data = json_decode($payload, true);

			// add the users from users
			if (!empty($data['users'])) {
				foreach ($data['users'] as $user) {
					$existingEmails = array_map(function($u) {
						return $u['email'];
					}, $this->dataStore['users']);

					if (!in_array($user['email'], $existingEmails)) {
						$user['id'] = rand(1000, 9999);
						$this->dataStore['users'][$user['id']] = $user;
					}
				}
			}

			$owner = current(array_filter($this->dataStore['users'], function($user) use ($data) {
				return !empty($user['is_owner']);
			}));
			$users = array_values(array_filter($this->dataStore['users'], function($user) use ($data) {
				return in_array($user['email'], array_map(function($u) {
					return $u['email'];
				}, $data['users']));
			}));

			return new \Interspire_Http_Response(200, array(),
				json_encode(array(
					'access_token' => array(
						'client_id' => uniqid(),
						'client_secret' => uniqid(),
						'access_token' => uniqid(),
						'user' => $owner,
						'scope' => OAuthClient::getStoreScopes(),
					),
					'users' => $users,
				)));
		} else if (preg_match('/^\/stores\/(.+)\/users$/', $path, $matches)) {
            $userId = !empty($payload['uid']) ? $payload['uid'] : rand(1, 999);
            return new \Interspire_Http_Response(200, array(),
                json_encode(array('user' => array('id' => $userId))));
        } else if ($path == '/users/verify') {

            $q = json_decode($payload, true);

            if (!empty($q['email'])) {
                $users = array_filter($this->dataStore['users'], function($user) use ($q) {
                    return $user['email'] == $q['email'];
                });

                if (empty($users)) {
                    throw new \Interspire_Http_ClientError('User not found', 404);
                }

                $user = current($users);
                if (!empty($user['password']) && $user['password'] == $q['password']) {
                    return new \Interspire_Http_Response(200, array(),
                        json_encode(array('user' => $user)));
                } else {
                    throw new \Interspire_Http_ClientError('Invalid credentials', 400);
                }

            }
        }
		return null;
	}

    public function authServicePut($path, $payload)
    {
        if (preg_match('/^\/stores\/(.+)\/users\/([0-9]+)$/', $path, $matches)) {
            $userId = $matches[1];
            return new \Interspire_Http_Response(200, array(),
                json_encode(array('user' => array('id' => $userId))));
        }
        return null;
    }

    public function authServiceDelete($path, $query)
    {
        if (preg_match('/^\/stores\/(.+)\/users\/([0-9]+)$/', $path, $matches)) {
            $userId = $matches[1];
            return new \Interspire_Http_Response(200, array(),
                json_encode(array('user' => array('id' => $userId))));
        }
        return null;
    }

	public function createAuthService()
	{
		$response = null;
		$that = $this;
		$httpClient = $this->getMock('\Interspire_Http_Client', array('post', 'get', 'put', 'delete', 'getResponse'));

		// mock get
		$httpClient->expects($this->any())->method('get')
			->withAnyParameters()
			->will($this->returnCallback(function($uri, $query = null) use (&$response, $httpClient, $that) {
				$parts = parse_url($uri);
				$response = $that->authServiceGet($parts['path'], (isset($parts['query']) ? $parts['query'] : $query));
				return $httpClient;
			}));

		// mock post
		$httpClient->expects($this->any())->method('post')
			->withAnyParameters()
			->will($this->returnCallback(function($uri, $payload = null) use (&$response, $httpClient, $that) {
				$parts = parse_url($uri);
				$response = $that->authServicePost($parts['path'], $payload);
				return $httpClient;
			}));

        // mock put
        $httpClient->expects($this->any())->method('put')
            ->withAnyParameters()
            ->will($this->returnCallback(function($uri, $payload = null) use (&$response, $httpClient, $that) {
                $parts = parse_url($uri);
                $response = $that->authServicePut($parts['path'], $payload);
                return $httpClient;
            }));

        // mock delete
        $httpClient->expects($this->any())->method('delete')
            ->withAnyParameters()
            ->will($this->returnCallback(function($uri, $query = null) use (&$response, $httpClient, $that) {
                $parts = parse_url($uri);
                $response = $that->authServiceDelete($parts['path'], (isset($parts['query']) ? $parts['query'] : $query));
                return $httpClient;
            }));

		$httpClient->expects($this->any())->method('getResponse')
			->withAnyParameters()
			->will($this->returnCallback(function() use (&$response) {
				return $response;
			}));

		$authService = $this->getMock('\Services\Bigcommerce\Auth\AuthService', array('getHttpClient'), array(uniqid(), uniqid(), uniqid()));
		$authService->expects($this->any())
			->method('getHttpClient')
			->withAnyParameters()
			->will($this->returnValue($httpClient));
		return $authService;
	}

	public function resetDataStore()
	{
		$this->dataStore = array(
			'users' => array(),
			'scopes' => array(),
			'authorizations' => array(),
		);
	}

	public function testCreateAuthService()
	{
		$this->assertNotEmpty($this->createAuthService());
	}

}
