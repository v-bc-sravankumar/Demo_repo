<?php

namespace Integration\Controllers\Storefront;

use Storefront\SearchController;
use Search\Log;
use Store\Settings\InventorySettings;
use Repository\SearchCorrections;
use Bigcommerce\SearchClient\Result\Result;
use Bigcommerce\SearchClient\Hit\Hit;
use Bigcommerce\SearchClient\Document\ProductDocument;

/**
 * @group nosample
 */
class SearchControllerTest extends \PHPUnit_Framework_TestCase
{
    private function getController($config = array(), $methods = null)
    {
        $app = $GLOBALS['app'];

        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($config));
        $settings->load();

        $inventorySettings = new InventorySettings(array(), $settings);
        $inventorySettings->load();

        return $this->getMockBuilder('\Storefront\SearchController')
            ->setConstructorArgs(array(
                $app['search.storefront.searcher.decorated'],
                $GLOBALS['ISC_CLASS_TEMPLATE'],
                $app['storefront.shopper'],
                $app['language.manager.storefront'],
                $settings,
                $inventorySettings,
                $app['search.result_builders.quick_searcher.helper'],
                new SearchCorrections(),
                $app['statsd.client']
            ))
            ->setMethods($methods)
            ->getMock();
    }

    public function testAjaxSearchActionReturnsXmlView()
    {
        $query = 'my search';
        $parsedQuery = 'parsed query';

        $result = new Result(
            $this->getMock('\Bigcommerce\SearchClient\Hit\HitParserInterface'),
            new \ArrayIterator(),
            25
        );

        $request = new \Interspire_Request(array('search_query' => $query));

        $controller = $this->getController(array('CharacterSet' => 'UTF-3000'), array('performQuickSearch'));
        $controller
            ->expects($this->once())
            ->method('performQuickSearch')
            ->with($this->equalTo($query))
            ->will($this->returnValue(array($result, $parsedQuery)));

        $controller->setRequest($request);

        $view = $controller->ajaxsearchAction();

        $this->assertInstanceOf('Interspire_Action_View', $view);
        $this->assertEquals('text/xml; charset=UTF-3000', $view->getContentType());
    }

    public function testAjaxSearchEventData()
    {
        $query = 'my search';
        $parsedQuery = 'parsed query';

        $parser = $this->getMock('\Bigcommerce\SearchClient\Hit\HitParserInterface');
        $parser
            ->expects($this->any())
            ->method('parse')
            ->will($this->returnValue(new Hit(new ProductDocument())));

        $result = new Result(
            $parser,
            new \ArrayIterator(array_fill(0, 30, 0)),
            100,
            null,
            50
        );

        $request = new \Interspire_Request(array('search_query' => $query));

        $controller = $this->getController(array('ShopPathNormal' => 'http://mystore.com'), array('performQuickSearch'));
        $controller
            ->expects($this->once())
            ->method('performQuickSearch')
            ->with($this->equalTo($query))
            ->will($this->returnValue(array($result, $parsedQuery)));

        $controller->setRequest($request);

        $triggered = false;
        $self = $this;
        \Interspire_Event::bind(
            \Store_Event::EVENT_SEARCH_STOREFRONT_QUICKSEARCH,
            function ($event) use (&$triggered, $self) {
                $expected = array(
                    'search_stats'      => array(
                        'all' => array(
                            'results' => 30,
                            'duration' => 50,
                        ),
                        'total' => array(
                            'results' => 30,
                            'duration' => 50,
                        ),
                    ),
                    'provider' => 'elastic',
                    'domain' => 'http://mystore.com',
                );

                $data = $event->data;

                $self->assertInternalType('float', $data['request_duration']);

                unset($data['request_duration']);

                $self->assertEquals($expected, $data);

                $triggered = true;
            }
        );

        $view = $controller->ajaxsearchAction();

        $this->assertTrue($triggered, 'quicksearch event was not triggered');
    }

    public function testTrackSearchClickAction()
    {
        $log = new Log();
        $log
            ->setSearchQuery('foo')
            ->setResultCount(5)
            ->setHasClickThrough(false)
            ->setDateCreated(time());

        if (!$log->save()) {
            $this->fail('Failed to save search log');
        }

        $request = new \Interspire_Request(array('searchid' => $log->getId()));

        $controller = $this->getController();
        $controller->setRequest($request);

        $view = $controller->tracksearchclickAction();

        $this->assertInstanceOf('Interspire_Action_View', $view);
        $this->assertEquals('text/javascript', $view->getContentType());
        $this->assertEquals('/* 1 */', $view->getData());

        $log->load();

        $this->assertTrue($log->getHasClickThrough());

        $log->delete();
    }
}
