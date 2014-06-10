<?php

namespace Integration\Webhooks;

use Config\Environment;
use Config\Properties;
use Interspire_Event;
use MessageQueue\Message;
use Store_Config;
use Webhooks\EventDispatcher;
use Webhooks\EventHandler;

class EventHandlerTest extends \Interspire_IntegrationTest
{

    protected $mockQueue = array();

    protected $originalConfigs = array();

    protected $currentHandler = null;

    public function setUp()
    {
        $preserveList = array(
            'StoreHash',
            'Feature_Webhooks',
            'Feature_WebhooksProduction',
            'Feature_WebhooksResque',
        );
        foreach ($preserveList as $preserveKey) {
            $this->originalConfigs[$preserveKey] = Store_Config::get($preserveKey);
        }
        Store_Config::override('StoreHash', 'xxx');
        Store_Config::override('Feature_WebhooksResque', false);
        $properties = new Properties(array(
            'webhooks' => array(
                'enabled' => true,
            ),
        ));
        Environment::override($properties->extend(Environment::export()));

        // reset the mock queue
        $this->mockQueue = array();

        \Store::getStoreDb()->StartTransaction();
    }

    public function tearDown()
    {
        if ($this->currentHandler) {
            $events = array_keys(EventHandler::$storeEventsMapping);
            foreach ($events as $event) {
                Interspire_Event::unbind($event, $this->currentHandler);
            }
        }

        \Store::getStoreDb()->RollbackTransaction();
        // reload config after all the munging
        foreach ($this->originalConfigs as $key => $value) {
            Store_Config::override($key, $value);
        }
        Environment::restore();
    }

    /**
     * @return EventHandler
     */
    protected function createEventHandler()
    {
        $mockQueue = &$this->mockQueue;

        $mockDriver = $this->getMock('MessageQueue\\Driver\\RabbitMQDriver', array('publish'));
        $mockDriver->expects($this->any())
            ->method('publish')
            ->withAnyParameters()
            ->will(
                $this->returnCallback(
                    function ($topic, Message $msg, $exchange = '', $persistent = false) use (&$mockQueue) {
                        if (empty($mockQueue[$topic])) {
                            $mockQueue[$topic] = array();
                        }
                        $mockQueue[$topic][] = json_decode($msg->getBody(), true);
                    }
                )
            );

        $eventDispatcher = $this->getMock('Webhooks\\EventDispatcher', array('getRabbitMQDriver'));
        $eventDispatcher->expects($this->any())
            ->method('getRabbitMQDriver')
            ->withAnyParameters()
            ->will($this->returnValue($mockDriver));

        $eventHandler = $this->getMock('Webhooks\\EventHandler', array('getEventDispatcher'));
        $eventHandler->expects($this->any())
            ->method('getEventDispatcher')
            ->withAnyParameters()
            ->will($this->returnValue($eventDispatcher));

        return $eventHandler;
    }

    public function testPublishBeta()
    {
        Store_Config::override('Feature_Webhooks', true);
        Store_Config::override('Feature_WebhooksProduction', false);

        // bind events
        $this->currentHandler = array($this->createEventHandler(), 'handleEvent');
        Interspire_Event::bind(array_keys(EventHandler::$storeEventsMapping), $this->currentHandler);

        foreach (EventHandler::$storeEventsMapping as $event => $mappedEvent) {
            Interspire_Event::trigger($event, array(EventHandler::getEventsIdMapping($event) => rand(1, 999)));
        }
        $this->assertEquals(
            count(EventHandler::$storeEventsMapping),
            count($this->mockQueue[EventDispatcher::MQ_TOPIC])
        );

    }

    public function testPublishProduction()
    {
        Store_Config::override('Feature_WebhooksProduction', true);

        // bind events
        $this->currentHandler = array($this->createEventHandler(), 'handleEvent');
        Interspire_Event::bind(array_keys(EventHandler::$storeEventsMapping), $this->currentHandler);

        foreach (EventHandler::$storeEventsMapping as $event => $mappedEvent) {
            Interspire_Event::trigger($event, array(EventHandler::getEventsIdMapping($event) => rand(1, 999)));
        }
        $this->assertEquals(
            count(EventHandler::$storeEventsMapping),
            count($this->mockQueue[EventDispatcher::MQ_TOPIC])
        );

        // test events has new version of producer
        foreach ($this->mockQueue[EventDispatcher::MQ_TOPIC] as $payload) {
            $this->assertTrue(strpos($payload['producer'], 'stores/xxx') === 0);
        }


    }

    public function testPublishBetaProduction()
    {
        Store_Config::override('Feature_Webhooks', true);
        Store_Config::override('Feature_WebhooksProduction', true);

        // bind events
        $this->currentHandler = array($this->createEventHandler(), 'handleEvent');
        Interspire_Event::bind(array_keys(EventHandler::$storeEventsMapping), $this->currentHandler);

        foreach (EventHandler::$storeEventsMapping as $event => $mappedEvent) {
            Interspire_Event::trigger($event, array(EventHandler::getEventsIdMapping($event) => rand(1, 999)));
        }
        $this->assertEquals(
            count(EventHandler::$storeEventsMapping)*2,
            count($this->mockQueue[EventDispatcher::MQ_TOPIC])
        );

    }
}
