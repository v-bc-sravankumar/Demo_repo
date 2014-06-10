<?php

namespace Unit\ContentRetriever;

use ContentRetriever\ContentRetriever;

class ContentRetrieverTest extends \PHPUnit_Framework_TestCase
{
    private function getRequest($requestCount = null)
    {
        $server = array();

        if ($requestCount !== null) {
            $server['HTTP_X_BIGCOMMERCE_INTERNAL'] = $requestCount;
        }

        return new \Interspire_Request(array(), array(), array(), $server);
    }

    /**
     * @expectedException \ContentRetriever\ThresholdExceededException
     * @expectedExceptionMessage The request to the url "http://foo.com" exceeded the limit of 3 requests.
     */
    public function testRetrieveThrowsExceptionIfThresholdExceeded()
    {
        $retriever = new ContentRetriever();
        $retriever->retrieve('http://foo.com', $this->getRequest(4));
    }

    public function testRetrieverPerformsRequestIfThresholdNotExceeded()
    {
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(new \Guzzle\Http\Message\Response(200));
        $client = new \Guzzle\Http\Client();
        $client->addSubscriber($plugin);

        $retriever = new ContentRetriever($client);
        $response = $retriever->retrieve('http://foo.com', $this->getRequest(3));

        $this->assertInstanceOf('\Guzzle\Http\Message\Response', $response);
    }

    public function testRetrieveIncrementsRequestCountHeader()
    {
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(new \Guzzle\Http\Message\Response(200));
        $client = new \Guzzle\Http\Client();
        $client->addSubscriber($plugin);


        $retriever = new ContentRetriever($client);
        $response = $retriever->retrieve('http://foo.com', $this->getRequest(1));

        $request = current($plugin->getReceivedRequests());
        $this->assertEquals(2, (string)$request->getHeader('X-BIGCOMMERCE-INTERNAL'));
    }
}
