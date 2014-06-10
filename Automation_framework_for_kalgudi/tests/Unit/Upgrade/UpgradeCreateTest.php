<?php
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Console\Commands\Upgrade\CreateCommand;

class UpgradeCommandsTest extends PHPUnit_Framework_TestCase
{

	static $time;

	public function setUp()
	{
		self::$time = date('YmdHis');
	}

	public function testFailsWithInvalidName()
	{
		$application = new Application();
		$application->add(new CreateCommand());
		$command = $application->find('upgrade:create');

		$commandTester = new CommandTester($command);
		$commandTester->execute(
			array('command' => $command->getName(), '--name' => -1)
		);

		$this->assertRegExp('/Invalid name/', $commandTester->getDisplay());
	}

	public function testFailsWithNameLessThanCurrentVersion()
	{
		$application = new Application();
		$application->add(new CreateCommand());
		$command = $application->find('upgrade:create');

		$commandTester = new CommandTester($command);
		$commandTester->execute(
			array('command' => $command->getName(), '--name' => 7500)
		);

		$this->assertRegExp('/must be higher/', $commandTester->getDisplay());
	}

	public function testCorrectMessageIfFileNotSaved()
	{
		$mock = $this->getMock('Console\Commands\Upgrade\CreateCommand', array('createUpgradeStepFile', 'setRequiredVersion', 'addVersionArrayElement'));
		$mock->expects($this->any())->method('createUpgradeStepFile')->will($this->returnValue(false));
		$mock->expects($this->any())->method('setRequiredVersion')->will($this->returnValue(true));
		$mock->expects($this->any())->method('addVersionArrayElement')->will($this->returnValue(true));

		$application = new Application();
		$application->add($mock);
		$command = $application->find('upgrade:create');

		$commandTester = new CommandTester($command);
		$commandTester->execute(
			array(
				'command' => $command->getName(),
				'--name' => self::$time
			)
		);

		$this->assertRegExp('/Could not write upgrade step/', $commandTester->getDisplay());
	}

	public function testUpgradeMessageOnSuccess()
	{
		$mock = $this->getMock('Console\Commands\Upgrade\CreateCommand', array('createUpgradeStepFile', 'setRequiredVersion', 'addVersionArrayElement'));
		$mock->expects($this->any())->method('createUpgradeStepFile')->will($this->returnValue(true));
		$mock->expects($this->any())->method('setRequiredVersion')->will($this->returnValue(true));
		$mock->expects($this->any())->method('addVersionArrayElement')->will($this->returnValue(true));

		$application = new Application();
		$application->add($mock);
		$command = $application->find('upgrade:create');

		$commandTester = new CommandTester($command);
		$commandTester->execute(
			array(
				'command' => $command->getName(),
				'--name' => self::$time
			)
		);

		$this->assertRegExp('/Upgrade step created/', $commandTester->getDisplay());
	}

	public function testCreateUpgradeSetsVersionConstant()
	{
		$currentVersion = PRODUCT_VERSION_CODE;

		$mock = $this->getMock('Console\Commands\Upgrade\CreateCommand', array('createUpgradeStepFile', 'addVersionArrayElement'));
		$mock->expects($this->any())->method('createUpgradeStepFile')->will($this->returnValue(true));
		$mock->expects($this->any())->method('addVersionArrayElement')->will($this->returnValue(true));

		$application = new Application();
		$application->add($mock);
		$command = $application->find('upgrade:create');

		$commandTester = new CommandTester($command);
		$commandTester->execute(
			array(
				'command' => $command->getName(),
				'--name' => self::$time
			)
		);

		$constantsFile = ISC_BASE_PATH . '/config/init/constants.php';
		$contents = file_get_contents($constantsFile);

		preg_match("#define\('PRODUCT_VERSION_CODE', ([0-9]+)\);#", $contents, $matches);
		$this->assertEquals(self::$time, $matches[1]);

		$contents = str_replace("define('PRODUCT_VERSION_CODE', " . self::$time . ");", "define('PRODUCT_VERSION_CODE', " . $currentVersion . ");", $contents);
		file_put_contents($constantsFile, $contents);
	}

	public function testCreateUpgradeAddsVersionToUpgradeClassArray()
	{
		$currentVersion = PRODUCT_VERSION_CODE;

		$mock = $this->getMock('Console\Commands\Upgrade\CreateCommand', array('createUpgradeStepFile', 'setRequiredVersion'));
		$mock->expects($this->any())->method('createUpgradeStepFile')->will($this->returnValue(true));
		$mock->expects($this->any())->method('setRequiredVersion')->will($this->returnValue(true));

		$application = new Application();
		$application->add($mock);
		$command = $application->find('upgrade:create');

		$commandTester = new CommandTester($command);
		$commandTester->execute(
			array(
				'command' => $command->getName(),
				'--name' => self::$time
			)
		);

		$upgradeFile = ISC_BASE_PATH . '/admin/includes/classes/class.upgrade.php';
		$contents = file_get_contents($upgradeFile);

		$versionElement = self::$time . " => '" . PRODUCT_VERSION . "',\n\t\t//next";

		$this->assertEquals(1, preg_match('#' . preg_quote($versionElement) . '#', $contents));

		$contents = str_replace("\t\t" . self::$time . " => '" . PRODUCT_VERSION . "',\n", "", $contents);
		file_put_contents($upgradeFile, $contents);
	}
}
