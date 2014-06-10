<?php

use MessageQueue\Driver\Driver;
use MessageQueue\Driver\RabbitMQDriver;

use MessageQueue\Message;

use MessageQueue\Service as MQ;
use MessageQueue\Router;

use MessageQueue\Exception\ConnectionException;

putenv("RABBITMQ_SSL_ENABLED=0");
putenv("RABBITMQ_URI=".TEST_RABBITMQ_URI);
putenv("RABBITMQ_USER=".TEST_RABBITMQ_USER);
putenv("RABBITMQ_PASSWORD=".TEST_RABBITMQ_PASSWORD);

class MessageQueue_PubSubRabbitMQ extends Interspire_IntegrationTest
{
	protected static $key = 'mq_debug_test';
	protected static $routes = null;
	protected static $router = null;

	protected static function getKeyedName($name) {
		return self::$key."_".$name;
	}

	protected $stopWaiting = false;

	protected $mq;
	protected $driver;
	protected static $mockQueue;
	protected static $mockBindings;
	protected static $subscribers = array();

	/**
	 * Gets the MessageQueue Service instance.
	 *
	 * @return MQ
	 */
	protected function getService()
	{
		try {
			$mq = MQ::createInstance($this->getDriver());
			$mq->connect();
			$this->mq = $mq;
			return $mq;
		}
		catch (ConnectionException $exception) {
			$this->markTestSkipped('Could not connect to RabbitMQ: ' . $exception->getMessage());
		}
	}

	/**
	 * Gets the RabbitMQ Driver instance.
	 *
	 * @return RabbitMQDriver
	 */
	public function getDriver()
	{
		$mockQueue = &self::$mockQueue;
		$mockBindings = &self::$mockBindings;
		$subscribers = &self::$subscribers;


		$mockChannel = $this->getMock('\\stdClass', array('wait', 'exchange_declare', 'queue_declare'));

		$mockChannel->expects($this->any())
			->method('wait')
			->withAnyParameters()
			->will($this->returnCallback(function() use (&$mockQueue, &$mockBindings, &$subscribers) {
				/* @var Message $message */
				foreach ($mockQueue as $exchange => $exchangeTopics) {
					foreach ($exchangeTopics as $topic => $messages) {
						foreach ($messages as $message) {
							// figure out which queue
							if (empty($mockBindings[$exchange])) {
								continue;
							}
							foreach ($mockBindings[$exchange] as $bindTopic => $queue) {
								if ($bindTopic == $topic || (preg_match('/#$/', $bindTopic) && strpos($bindTopic, $topic) ===0)) {
									if (empty($subscribers[$queue])) {
										continue;
									}
									foreach ($subscribers[$queue] as $callback) {
										call_user_func($callback, $message);
									}
								}
							}
						}
					}
				}
			}));

		$mockDriver = $this->getMock('MessageQueue\\Driver\\RabbitMQDriver',
			array('connect', 'bindQueueToExchange', 'publish', 'subscribe', 'cancelSubscriber', 'getChannel'));

		$mockDriver->expects($this->any())
			->method('getChannel')
			->withAnyParameters()
			->will($this->returnValue($mockChannel));

		$mockDriver->expects($this->any())
			->method('publish')
			->withAnyParameters()
			->will($this->returnCallback(function($topic, Message $msg, $exchange = '', $persistent = false) use (&$mockQueue, &$mockBindings) {
				if (empty($mockQueue[$exchange])) {
					$mockQueue[$exchange] = array();
				}
				if (empty($mockQueue[$exchange][$topic])) {
					$mockQueue[$exchange][$topic] = array();
				}
				$mockQueue[$exchange][$topic][] = json_decode($msg->getBody(), true);
			}));

		$mockDriver->expects($this->any())
			->method('bindQueueToExchange')
			->withAnyParameters()
			->will($this->returnCallback(function($exchange, $queue, $topic) use (&$mockBindings) {
				if (!isset($mockBindings[$exchange])) {
					$mockBindings[$exchange] = array();
				}
				$mockBindings[$exchange][$topic] = $queue;
			}));

		$mockDriver->expects($this->any())
			->method('subscribe')
			->withAnyParameters()
			->will($this->returnCallback(function($queue, $id, $callback) use (&$subscribers) {
				if (!isset($subscribers[$queue])) {
					$subscribers[$queue] = array();
				}
				$subscribers[$queue][$id] = $callback;
			}));

		$mockDriver->expects($this->any())
			->method('cancelSubscriber')
			->withAnyParameters()
			->will($this->returnCallback(function($queue, $id) use (&$subscribers) {
				if (!empty($subscribers[$queue][$id])) {
					unset($subscribers[$queue][$id]);
				}
			}));

		return $mockDriver;

	}

	/**
	 * Set the stopWaiting flag
	 *
	 * @param bool|string $bool
	 */
	public function setStopWaiting($bool = false)
	{
		$this->stopWaiting = !empty($bool);
	}

	/**
	 * Setup
	 */
	public function setUp()
	{
		$this->stopWaiting = false;
		// reset the mock queue
		self::$mockQueue = array();
		self::$subscribers = array();
	}

	public function tearDown()
	{
		if ($this->mq) {
			try {
				$this->mq->close();
			}
			catch (Exception $exception) {
			}
			$this->mq = null;
		}

		if ($this->driver) {
			try {
				$this->driver->close();
			}
			catch (Exception $exception) {
			}
			$this->driver = null;
		}
	}

	/**
	 * Static setup
	 */
	public static function setUpBeforeClass()
	{

		$instance = new static;
		self::$key = str_replace('.', '', uniqid('mq'.time().'_', true));
		self::$router = Router::getInstance($instance->getDriver());

		self::$routes = array(
			"exchanges" => array(
				array("name" => self::getKeyedName("pubsub"), "type" => "topic", "durable" => true, "autoDelete" => false),
			),
			"queues" => array(
				array("name" => self::getKeyedName("queue_all"), "durable" => true, "autoDelete" => false),
				array("name" => self::getKeyedName("queue_basic"), "durable" => true, "autoDelete" => false),
				array("name" => self::getKeyedName("queue_sub"), "durable" => true, "autoDelete" => false),
				array("name" => self::getKeyedName("queue_sub_topic1"), "durable" => true, "autoDelete" => false),
				array("name" => self::getKeyedName("queue_parallel1"), "durable" => true, "autoDelete" => false),
				array("name" => self::getKeyedName("queue_parallel2"), "durable" => true, "autoDelete" => false),
			),
			"bindings" => array(
				array("exchange" => self::getKeyedName("pubsub"), "queue" => self::getKeyedName("queue_all"), "topic" => "test.#"),
				array("exchange" => self::getKeyedName("pubsub"), "queue" => self::getKeyedName("queue_basic"), "topic" => "test.basic"),
				array("exchange" => self::getKeyedName("pubsub"), "queue" => self::getKeyedName("queue_sub"), "topic" => "test.sub.#"),
				array("exchange" => self::getKeyedName("pubsub"), "queue" => self::getKeyedName("queue_sub_topic1"), "topic" => "test.sub.topic1"),
				array("exchange" => self::getKeyedName("pubsub"), "queue" => self::getKeyedName("queue_parallel1"), "topic" => "test.parallel"),
				array("exchange" => self::getKeyedName("pubsub"), "queue" => self::getKeyedName("queue_parallel2"), "topic" => "test.parallel"),
			)
		);
		try {
			self::$router->setRoutingMap(self::$routes);
			self::$router->initRoutes();
		} catch (Exception $e) {
			// fail silently
		}
	}

	/**
	 * Test a single message in the pipeline
	 */
	public function testSingleMessage()
	{
		$msg = array("message" => "hello world");
		$mq = $this->getService();
		$mq->publish("test.basic", new Message(json_encode($msg)), self::getKeyedName("pubsub"), true);

		$driver = $this->getDriver();
		$that = $this;

		$driver->subscribe(self::getKeyedName("queue_basic"), "consumer1", function(Message $mqmsg) use ($msg, $that) {
			$mqmsg->acknowledge();
			$that->assertEquals($msg, json_decode($mqmsg->getBody(), true));
			$that->setStopWaiting(true);
		});

		$startTime = time();

		$ch = $driver->getChannel();
		while (count($ch->callbacks)) {
			if ($this->stopWaiting) {
				$driver->cancelSubscriber(self::getKeyedName("queue_basic"), "consumer1");
				break;
			}
			$ch->wait(null, true);
		}
	}

	/**
	 * Test multiple messages
	 */
	public function testMultipleMessages()
	{
		$messages = array(
			array("message" => "mesage 1"),
			array("message" => "mesage 2"),
			array("message" => "mesage 3"),
			array("message" => "mesage 4"),
			array("message" => "mesage 5"),
			array("message" => "mesage 6"),
			array("message" => "mesage 7"),
			array("message" => "mesage 8"),
			array("message" => "mesage 9"),
			array("message" => "mesage 10"),
		);

		$mq = $this->getService();
		foreach ($messages as $msg) {
			$mq->publish("test.basic", new Message(json_encode($msg)), self::getKeyedName("pubsub"), true);
		}

		$driver = $this->getDriver();

		$received = array();

		$startTime = time();

		$driver->subscribe(self::getKeyedName("queue_basic"), "consumer1", function(Message $mqmsg) use (&$received) {
			$mqmsg->acknowledge();
			$received[] = json_decode($mqmsg->getBody(), true);
		});

		$ch = $driver->getChannel();
		while (count($ch->callbacks)) {

			if (count($received) == count($messages)) {
				$this->assertEquals($messages, $received);
				$driver->cancelSubscriber(self::getKeyedName("queue_basic"), "consumer1");
				break;
			}
			$ch->wait(null, true);
		}
	}

	/**
	 * Test Parallel messages
	 */
	public function testParallelMessages()
	{
		$messages = array(
				array("message" => "mesage 1"),
				array("message" => "mesage 2"),
				array("message" => "mesage 3"),
				array("message" => "mesage 4"),
				array("message" => "mesage 5"),
				array("message" => "mesage 6"),
				array("message" => "mesage 7"),
				array("message" => "mesage 8"),
				array("message" => "mesage 9"),
				array("message" => "mesage 10"),
		);

		$mq = $this->getService();
		foreach ($messages as $msg) {
			$mq->publish("test.parallel", new Message(json_encode($msg)), self::getKeyedName("pubsub"), true);
		}

		$driver = $this->getDriver();

		$received1 = array();
		$received2 = array();

		$startTime = time();

		$driver->subscribe(self::getKeyedName("queue_parallel1"), "consumer1", function(Message $mqmsg) use (&$received1) {
			$mqmsg->acknowledge();
			$received1[] = json_decode($mqmsg->getBody(), true);
		});

		$driver->subscribe(self::getKeyedName("queue_parallel2"), "consumer2", function(Message $mqmsg) use (&$received2) {
			$mqmsg->acknowledge();
			$received2[] = json_decode($mqmsg->getBody(), true);
		});

		$ch = $driver->getChannel();
		while (count($ch->callbacks)) {
			if (count($received1) == count($messages) && count($received2) == count($messages)) {
				$this->assertEquals($messages, $received1);
				$this->assertEquals($messages, $received2);
				$driver->cancelSubscriber(self::getKeyedName("queue_parallel1"), "consumer1");
				$driver->cancelSubscriber(self::getKeyedName("queue_parallel2"), "consumer2");
				break;
			}
			$ch->wait(null, true);
		}
	}

	/**
	 * Test SubTopic messages
	 */
	public function testSubtopicMessages()
	{
		$messages = array(
				array("message" => "mesage 1"),
				array("message" => "mesage 2"),
				array("message" => "mesage 3"),
				array("message" => "mesage 4"),
				array("message" => "mesage 5"),
				array("message" => "mesage 6"),
				array("message" => "mesage 7"),
				array("message" => "mesage 8"),
				array("message" => "mesage 9"),
				array("message" => "mesage 10"),
		);

		$mq = $this->getService();
		foreach ($messages as $msg) {
			$mq->publish("test.sub.topic1", new Message(json_encode($msg)), self::getKeyedName("pubsub"), true);
		}

		$driver = $this->getDriver();

		$received1 = array();
		$received2 = array();

		$startTime = time();

		$driver->subscribe(self::getKeyedName("queue_sub"), "consumer1", function(Message $mqmsg) use (&$received1) {
			$mqmsg->acknowledge();
			$received1[] = json_decode($mqmsg->getBody(), true);
		});

		$driver->subscribe(self::getKeyedName("queue_sub_topic1"), "consumer2", function(Message $mqmsg) use (&$received2) {
			$mqmsg->acknowledge();
			$received2[] = json_decode($mqmsg->getBody(), true);
		});

		$ch = $driver->getChannel();
		while (count($ch->callbacks)) {
			if (count($received1) == count($messages) && count($received2) == count($messages)) {
				$this->assertEquals($messages, $received1);
				$this->assertEquals($messages, $received2);
				$driver->cancelSubscriber(self::getKeyedName("queue_sub"), "consumer1");
				$driver->cancelSubscriber(self::getKeyedName("queue_sub_topic1"), "consumer2");
				break;
			}
			$ch->wait(null, true);
		}
	}
}
