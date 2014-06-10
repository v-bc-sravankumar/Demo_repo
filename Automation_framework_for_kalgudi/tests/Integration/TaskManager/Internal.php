<?php

require_once dirname(__FILE__).'/Job.php';


class Unit_TaskManager_Internal extends Interspire_IntegrationTest
{
	/**
	 * As the task manager system is based on static methods, to test it we need some
	 * publicly accessible reference back to phpunit class instances and associated data.
	 * This is reset on every test by setUp.
	 *
	 * @var Unit_TaskManager_Internal
	 */
	public static $currentTestInstance;

	/**
	 * As above, but storage for arbitrary data.
	 *
	 * @var array
	 */
	public static $currentTestData;

	public function setUp ()
	{
		self::$currentTestInstance = $this;
		self::$currentTestData = array();
		require_once BUILD_ROOT . '/admin/init.php';
		// clear out task manager state in between tests
		while (Interspire_TaskManager_Internal::executeNextTask() !== null);
	}

	/**
	* @expectedException Interspire_TaskManager_InvalidCallbackException
	*/
	public function testJobClassDoesntExist ()
	{
		Interspire_TaskManager_Internal::createTask('test', 'InvalidClassName');
	}

	/**
	* @expectedException Interspire_TaskManager_InvalidCallbackException
	*/
	public function testJobClassHasNoPerformMethod ()
	{
		Interspire_TaskManager_Internal::createTask('test', 'Job_Test_NoPerformMethod');
	}

	/**
	* @expectedException Interspire_TaskManager_InvalidArgumentException
	*/
	public function testJobQueueNameTooLong ()
	{
		Interspire_TaskManager_Internal::createTask(str_repeat('x', Interspire_TaskManager_Internal::MAX_QUEUE_NAME_LENGTH + 1), 'Job_Test_NoData');
	}

	/**
	* @expectedException Interspire_TaskManager_InvalidArgumentException
	*/
	public function testJobClassNameTooLong ()
	{
		// to pass initial checks, this class has to exist
		Interspire_TaskManager_Internal::createTask('test', 'Job_Test_LongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassName');
	}

	public function testSuccessfulTaskExecution ()
	{
		$randomValue = mt_rand();
		self::$currentTestData['randomValue'] = $randomValue;
		$taskId = Interspire_TaskManager_Internal::createTask('test', 'Job_Test_CheckRandomValue', array('randomValue' => $randomValue));
		$this->assertGreaterThan(0, $taskId);
		$this->assertTrue(Interspire_TaskManager_Internal::executeNextTask());
		$this->assertFalse(isset(self::$currentTestData['randomValue']), "randomValue is still set in currentTestData, which means checkRandomValueHandler did not run");
		$status = Interspire_TaskManager_Internal::getTaskStatus($taskId);
		$this->assertTrue($status->success);
	}

	public function testSuccessfulTaskExecutionWithNoData ()
	{
		$taskId = Interspire_TaskManager_Internal::createTask('test', 'Job_Test_NoData');
		$this->assertGreaterThan(0, $taskId);
		$this->assertTrue(Interspire_TaskManager_Internal::executeNextTask());
		$status = Interspire_TaskManager_Internal::getTaskStatus($taskId);
		$this->assertTrue($status->success);
	}

	public function testSuccessfulTaskExecutionWhenJobReturnsNull ()
	{
		$taskId = Interspire_TaskManager_Internal::createTask('test', 'Job_Test_ReturnsNull');
		$this->assertGreaterThan(0, $taskId);
		$this->assertTrue(Interspire_TaskManager_Internal::executeNextTask());
		$status = Interspire_TaskManager_Internal::getTaskStatus($taskId);
		$this->assertTrue($status->success);
	}

	public function testUnsuccessfulTask ()
	{
		$taskId = Interspire_TaskManager_Internal::createTask('test', 'Job_Test_Fail');
		$this->assertGreaterThan(0, $taskId);
		$this->assertFalse(Interspire_TaskManager_Internal::executeNextTask());
		$status = Interspire_TaskManager_Internal::getTaskStatus($taskId);
		$this->assertFalse($status->success);
	}

	public function testTaskWithException ()
	{
		$taskId = Interspire_TaskManager_Internal::createTask('test', 'Job_Test_ThrowException');
		$this->assertGreaterThan(0, $taskId);
		$this->assertFalse(Interspire_TaskManager_Internal::executeNextTask());
		$status = Interspire_TaskManager_Internal::getTaskStatus($taskId);
		$this->assertFalse($status->success);
	}

	public function testFirstInFirstOutTaskExecution ()
	{
		$tasks = array();

		$tasks[] = Interspire_TaskManager_Internal::createTask('test', 'Job_Test_NoData');
		$tasks[] = Interspire_TaskManager_Internal::createTask('test', 'Job_Test_NoData');
		$tasks[] = Interspire_TaskManager_Internal::createTask('test', 'Job_Test_NoData');

		Interspire_TaskManager_Internal::executeNextTask();

		$status = Interspire_TaskManager_Internal::getTaskStatus($tasks[0]);
		$this->assertTrue($status->success);
		$this->assertFalse(Interspire_TaskManager_Internal::getTaskStatus($tasks[1]));
		$this->assertFalse(Interspire_TaskManager_Internal::getTaskStatus($tasks[2]));

		Interspire_TaskManager_Internal::executeNextTask();

		$status = Interspire_TaskManager_Internal::getTaskStatus($tasks[1]);
		$this->assertTrue($status->success);
		$this->assertFalse(Interspire_TaskManager_Internal::getTaskStatus($tasks[2]));

		Interspire_TaskManager_Internal::executeNextTask();

		$status = Interspire_TaskManager_Internal::getTaskStatus($tasks[2]);
		$this->assertTrue($status->success);
	}

	public function testMultipleQueues ()
	{
		$taskA = Interspire_TaskManager_Internal::createTask('testA', 'Job_Test_NoData');
		$taskB = Interspire_TaskManager_Internal::createTask('testB', 'Job_Test_NoData');

		Interspire_TaskManager_Internal::executeNextTask('testB');
		$status = Interspire_TaskManager_Internal::getTaskStatus($taskB);
		$this->assertTrue($status->success);

		Interspire_TaskManager_Internal::executeNextTask('testA');
		$status = Interspire_TaskManager_Internal::getTaskStatus($taskA);
		$this->assertTrue($status->success);
	}

	public function testRepeatingTask ()
	{
		$task = Interspire_TaskManager_Internal::createTask('test', 'Job_Test_RepeatingTask', array('counter' => 3));
		$this->assertTrue(Interspire_TaskManager_Internal::executeNextTask(), "First iteration of repeating task failed");
		$this->assertTrue(Interspire_TaskManager_Internal::hasTasks(), "Call to hasTasks after first iteration failed");
		$this->assertTrue(Interspire_TaskManager_Internal::executeNextTask(), "Second iteration of repeating task failed");
		$this->assertTrue(Interspire_TaskManager_Internal::hasTasks(), "Call to hasTasks after second iteration failed");
		$this->assertTrue(Interspire_TaskManager_Internal::executeNextTask(), "Third iteration of repeating task failed");
		$this->assertTrue(Interspire_TaskManager_Internal::hasTasks(), "Call to hasTasks after third iteration failed");
		$this->assertFalse(Interspire_TaskManager_Internal::executeNextTask(), "Fourth iteration of repeating task failed");
		$this->assertFalse(Interspire_TaskManager_Internal::hasTasks(), "Call to hasTasks after fourth iteration failed");
		$this->assertEquals(null, Interspire_TaskManager_Internal::executeNextTask(), "Fifth iteration of repeating task occurred but should not have");
	}

	public function testNoTaskToExecute ()
	{
		$this->assertEquals(null, Interspire_TaskManager_Internal::executeNextTask());
	}

	public function testTaskWithInvalidDataFails ()
	{
		$task = Interspire_TaskManager_Internal::createTask('test', 'Job_Test_NoData');
		$this->assertTrue($this->fixtures->UpdateQuery('tasks', array('data' => '{invalidjson'), '`id`=' . (int)$task));
		$this->assertFalse(Interspire_TaskManager_Internal::executeNextTask());
	}

	public function testTaskWithNullDataKeepsNullData ()
	{
		$task = Interspire_TaskManager_Internal::createTask('test', 'Job_Test_VerifyNull', null);
		$this->assertTrue(Interspire_TaskManager_Internal::executeNextTask());
	}
}
