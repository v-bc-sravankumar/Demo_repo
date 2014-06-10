<?php

class Job_Test_NoPerformMethod
{

}

class Job_Test_CheckRandomValue extends Job_Store_Abstract
{
	public function perform ()
	{
		$instance = Unit_TaskManager_Internal::$currentTestInstance;
		$instance->assertTrue(isset($this->args['randomValue']), "randomValue is not set in data provided to handler");
		$instance->assertTrue(isset(Unit_TaskManager_Internal::$currentTestData['randomValue']), "randomValue is not set in test class static data");
		$instance->assertEquals($this->args['randomValue'], Unit_TaskManager_Internal::$currentTestData['randomValue']);
		unset(Unit_TaskManager_Internal::$currentTestData['randomValue']);
		return true;
	}
}

class Job_Test_ReturnsNull extends Job_Store_Abstract
{
	public function perform ()
	{

	}
}

class Job_Test_NoData extends Job_Store_Abstract
{
	public function perform ()
	{
		return true;
	}
}

// to pass initial checks, this class has to exist
class Job_Test_LongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassNameLongClassName extends Job_Test_NoData { }

class Job_Test_Fail extends Job_Store_Abstract
{
	public function perform ()
	{
		return false;
	}
}

class Job_Test_ThrowException extends Job_Store_Abstract
{
	public function perform ()
	{
		throw new Exception('This exception is expected.');
	}
}

class Job_Test_RepeatingTask extends Job_Store_Abstract
{
	public function perform ()
	{
		if ($this->args['counter']--) {
			Interspire_TaskManager_Internal::createTask('test', __CLASS__, array('counter' => $this->args['counter']));
			return true;
		}
		return false;
	}
}

class Job_Test_VerifyNull extends Job_Store_Abstract
{
	public function perform ()
	{
		return $this->args === null;
	}
}
