<?php

require_once dirname(__FILE__).'/Job.php';

/**
 * Repeating tasks should be logged. This takes a long time to run, so probably best not to
 * execute it via cli.
 *
 * The internal TaskManager is no longer used in production and due to the duration it takes to execute this test (~6 mins)
 * this test is now disabled. -RW
 *
 * @group disabled
 */
class Unit_TaskManager_StatusLog extends Interspire_IntegrationTest
{
	public function testStatusLogPruning ()
	{
		// ensure we're starting out with an empty log
		$GLOBALS['ISC_CLASS_DB']->Query("TRUNCATE `[|PREFIX|]task_status`");

		$task = Interspire_TaskManager_Internal::createTask('test', 'Job_Test_RepeatingTask', array('counter' => Interspire_TaskManager_Internal::TOTAL_LOGGED_TASKS + 15));
		while (Interspire_TaskManager_Internal::executeNextTask()) { };
		$count = $this->fixtures->FetchRow("SELECT COUNT(*) AS status_count FROM `[|PREFIX|]task_status`");
		$this->assertEquals(Interspire_TaskManager_Internal::TOTAL_LOGGED_TASKS, (int)$count['status_count']);
	}
}
