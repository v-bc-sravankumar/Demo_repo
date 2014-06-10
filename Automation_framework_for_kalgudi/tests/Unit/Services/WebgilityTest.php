<?php

require_once dirname(__FILE__).'/../../../config/init/autoloader.php';

class Unit_Services_WebgilityTest extends PHPUnit_Framework_TestCase
{
    protected $baseUri;
    protected $apiKey;

    public function setUp()
    {
        $this->baseUri = 'https://example'.rand(10,99).'/sample/';
        $this->apiKey  = 'ef5aa47a393ef6';
    }

    public function testSubscribeSuccess()
    {
        $testAccountId = 12345;

        // Pretend HTTP Client that \Services\Webgility will use to make requests with.
        // Set expectations of what will be sent to it, and have it return a dummy
        // response to pretend it's talking to Webgility.
        $client = $this->getMock('Interspire_Client', array('post', 'getBody'));
        $client->expects($this->once())
               ->method('post')
               ->with($this->baseUri, array(
                    'method'            => 'subscribe',
                    'apikey'            => $this->apiKey,
                    'mode'              => '', // Testing mode toggle
                    'first_name'        => 'Shieling',
                    'last_name'         => 'Xu',
                    'email'             => 's@xu.net',
                    'phone'             => '0299990000',
                    'password'          => 'asdfasdf',
                    'qb_version'        => 'qboe',
                    'product'           => 'ecc-cloud',
                    'subscription_plan' => 'ecc-cloud-trial',
                    'customer_type'     => 'trial',
                    'customer_plan'     => 'Bronze',
               ))
               ->will($this->returnValue(null));
        $client->expects($this->any())
               ->method('getBody')
               ->will($this->returnValue(json_encode(array(
                    'status'     => 'success',
                    'account-id' => $testAccountId,
               ))));

        // Make a request and check our dummy response was correctly interpreted.
        $service  = new \Services\Webgility($this->apiKey, $this->baseUri, $client);
        $responseAccountId = $service->subscribe(
            array(
                'first_name' => 'Shieling',
                'last_name'  => 'Xu',
                'email'      => 's@xu.net',
                'phone'      => '0299990000',
                'password'   => 'asdfasdf',
            ),
            $hasQuickBooksOnline = true,
            $planName = 'Bronze'
        );

        $this->assertEquals($responseAccountId, $testAccountId);
    }

    /**
     * @expectedException \Services\Webgility\Exception\InvalidApiKey
     */
    public function testSubscribeError()
    {
        $client = $this->getMock('Interspire_Client', array('post', 'getBody'));
        $client->expects($this->any())
               ->method('getBody')
               ->will($this->returnValue(json_encode(array(
                  'status'     => 'fail',
                  'error_code' => '101',
                  'error'      => 'Invalid apikey',
               ))));

        // Make a request and expect a bad-api-key error to be thrown.
        $service  = new \Services\Webgility($this->apiKey, $this->baseUri, $client);
        $responseAccountId = $service->subscribe(
            array(
                'first_name' => 'Shieling',
                'last_name'  => 'Xu',
                'email'      => 's@xu.net',
                'phone'      => '0299990000',
                'password'   => 'asdfasdf',
            ),
            $hasQuickBooksOnline = true,
            $planName = 'Bronze'
        );
    }

    public function testAuthenticateSuccess()
    {
        $testAccountId = 12345;

        $client = $this->getMock('Interspire_Client', array('post', 'getBody'));
        $client->expects($this->once())
               ->method('post')
               ->with($this->baseUri, array(
                    'method'        => 'authenticate_account',
                    'apikey'        => $this->apiKey,
                    'mode'          => '', // Testing mode toggle
                    'email'         => 's@xu.net',
                    'password'      => 'asdfasdf',
                    'customer_type' => 'trial',
                    'customer_plan' => 'Bronze',
               ))
               ->will($this->returnValue(null));
        $client->expects($this->any())
               ->method('getBody')
               ->will($this->returnValue(json_encode(array(
                  'status'     => 'success',
                  'account-id' => $testAccountId,
                  'customer_plan' => 'ecc-cloud-trial',
               ))));

        $service = new \Services\Webgility($this->apiKey, $this->baseUri, $client);
        $details = $service->authenticate('s@xu.net', 'asdfasdf', 'Bronze');

        $this->assertEquals(
            $details,
            array(
                'id' => $testAccountId,
                'quickbooks_online' => true,
            )
        );
    }

    public function testDisconnectSuccess()
    {
        $testAccountId = 12345;

        $client = $this->getMock('Interspire_Client', array('post', 'getBody'));
        $client->expects($this->once())
               ->method('post')
               ->with($this->baseUri, array(
                    'method'     => 'disconnect',
                    'apikey'     => $this->apiKey,
                    'mode'       => '', // Testing mode toggle
                    'account-id' => $testAccountId,
               ))
               ->will($this->returnValue(null));
        $client->expects($this->any())
               ->method('getBody')
               ->will($this->returnValue(json_encode(array(
                  'status'     => 'success',
                  'account-id' => $testAccountId,
               ))));

        $service = new \Services\Webgility($this->apiKey, $this->baseUri, $client);
        $this->assertEquals(
            $testAccountId,
            $service->disconnect($testAccountId)
        );
    }

    /**
     * Test that we correctly pass on the Interspire HTTP Client
     * errors back to the controller.
     *
     * @expectedException Interspire_Http_Exception
     */
    public function testConnectionError()
    {
        $client = $this->getMock('Interspire_Client', array('post', 'getBody'));
        $client->expects($this->once())
               ->method('post')
               ->will($this->throwException(new Interspire_Http_NetworkError('A Message')));
        $service = new \Services\Webgility($this->apiKey, $this->baseUri, $client);
        $service->request($this->buildMockAction());
    }

    /**
     * @expectedException \Services\Webgility\Exception\InvalidResponse
     */
    public function testInvalidJsonResponse()
    {
        // Prepare to send back rubbish.
        $client = $this->getMock('Interspire_Client', array('post', 'getBody'));
        $client->expects($this->once())->method('post')->will($this->returnValue(null));
        $client->expects($this->any())
               ->method('getBody')
               ->will($this->returnValue('VERY INVALID JSON, YO.'));

        // Request the aforementioned rubbish.
        $service = new \Services\Webgility($this->apiKey, $this->baseUri, $client);
        $service->request($this->buildMockAction());
    }

    public function testTestingModeToggle()
    {
        $client = $this->getMock('Interspire_Client', array('post', 'getBody'));
        $client->expects($this->any())
               ->method('getBody')
               ->will($this->returnValue(json_encode(array(
                  'status' => 'success',
               ))));

        // In the absence of working closures in 5.3, foreach() is the
        // best we have while still being able to use $this.
        foreach (array(true, false) as $isTest) {

            // Set the expectation to test: that we'll tell the action
            // it's in test mode or not in test mode.
            $action = $this->buildMockAction();
            $action->expects($this->once())
                   ->method('setTestingEnvironment')
                   ->with($isTest);

            // Fire off a post to trigger the test expectation.
            $service = new \Services\Webgility($this->apiKey, $this->baseUri, $client, $isTest);
            $service->request($action);
        }
    }


    protected function buildMockAction()
    {
        $action = $this->getMock('SomeAction', array(
            'setTestingEnvironment',
            'setApiKey',
            'buildRequestHash',
            'handleSuccess',
        ));
        $action->expects($this->any())
               ->method('buildRequestHash')
               ->will($this->returnValue(array(
                  'just has to be' => 'an array',
               )));
        return $action;
    }
}
