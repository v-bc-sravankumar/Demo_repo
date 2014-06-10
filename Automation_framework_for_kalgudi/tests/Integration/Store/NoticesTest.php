<?php
require_once BUILD_ROOT.'/admin/init.php';
use Store\Notices;
use Notices\AbstractNotice;
use Notices\TestNotice;
class Unit_Store_NoticesTest extends Interspire_IntegrationTest
{
    /**
     * @var \Store\Notices
     */
    protected $notices;

    public function setUp()
    {
        $this->notices = new Store\Notices();
    }

	public function testAdd()
	{
		$notices = $this->notices;

		$dummy = new \stdClass();
		$dummy->message = "abc";
		$notices->add('test', $dummy);

		$arr = $notices->get();

		$this->assertEquals(1, $notices->getCount());
		$this->assertEquals('abc', $arr['test']->message);
	}

	public function testDownForMaintenance()
	{
		$notices = $this->notices;
		$this->setupLegacyNotices($notices);

		\Store_Config::override('DownForMaintenance', 1);

		$notices->setLegacyNotices();
		$arr = $notices->get();

		$this->assertArrayHasKey('legacy',$arr);
		$this->assertEquals(1, count($arr['legacy']));

		foreach ($arr['legacy'] as $obj)
		{
			$this->assertFalse(empty($obj->message));
		}

		\Store_Config::override('DownForMaintenance', 0);
	}

	public function testCheckoutModulesInTest()
	{
		$notices = new Mock_Store_Notices1();
		$this->setupLegacyNotices($notices);

		$notices->setLegacyNotices();
		$arr = $notices->get();

		foreach ($arr['legacy'] as $obj)
		{
			$this->assertFalse(empty($obj->message));
		}
	}

	public function testLKNWarning()
	{
		$notices = $this->notices;
		$this->setupLegacyNotices($notices);

		$GLOBALS['LKN'] = '1';

		$notices->setLegacyNotices();
		$arr = $notices->get();

		$this->assertArrayHasKey('legacy',$arr);
		$this->assertEquals(1, count($arr['legacy']));

		foreach ($arr['legacy'] as $obj)
		{
			$this->assertFalse(empty($obj->message));
		}

		unset($GLOBALS['LKN']);
	}

	public function testControlPanelWarningMsg()
	{
		$notices = new Mock_Store_Notices2();
		$this->setupLegacyNotices($notices);

		$notices->setLegacyNotices();
		$arr = $notices->get();

		$this->assertArrayHasKey('legacy',$arr);
		$this->assertEquals(1, count($arr['legacy']));

		foreach ($arr['legacy'] as $obj)
		{
			$this->assertFalse(empty($obj->message));
			$this->assertEquals('This is a test warning message', $obj->message);
		}
	}

	public function testEmptyLegacy()
	{
		$notices = $this->notices;
		$this->setupLegacyNotices($notices);
		$notices->setLegacyNotices();
		$arr = $notices->get();

		$this->assertFalse(array_key_exists('legacy',$arr));
	}

	private function setupLegacyNotices($notices)
	{
		$mockAuth = $this->getMock('ISC_ADMIN_AUTH', array('HasPermission'));
		$mockAuth->expects($this->any())
		->method('HasPermission')
		->will($this->returnValue(true));

		$notices->setPermissionValidator($mockAuth);

		$file = ISC_BASE_PATH.'/language/'.Store_Config::get('Language').'/admin/common.ini';
		ParseLangFile($file, true);
	}

	public function testDisplayableVisibleNoticesAreAdded()
	{
		$notices = $this->notices;
		$notice = new TestNotice(TestNotice::STATE_VISIBLE);
        $notices->setAvailableNotices(array($notice->getName() => $notice));

		$notices->scheduleNotices();

        $this->assertArrayHasKey($notice->getName(), $notices->get());
	}

    public function testDisplayableHiddenNoticesAreAdded()
    {
        $notices = $this->notices;
        $notice = new TestNotice(TestNotice::STATE_HIDDEN);
        $notices->setAvailableNotices(array($notice->getName() => $notice));

        $notices->scheduleNotices();

        $this->assertArrayHasKey($notice->getName(), $notices->get());
    }

    public function testNonDisplayableNoticesAreNotAdded()
    {
        $notices = $this->notices;
        $notice = new TestNotice(TestNotice::STATE_HIDDEN, false);
        $notices->setAvailableNotices(array($notice->getName() => $notice));

        $notices->scheduleNotices();

        $this->assertArrayNotHasKey($notice->getName(), $notices->get());
    }

    public function testNotDisplayableNoticesAreReset()
    {
        $permissionValidator = $this->getMock('\ISC_ADMIN_AUTH');
        $permissionValidator->expects($this->once())->method('HasPermission')->will($this->returnValue(true));

        $keyStore = $this->getMock('\Interspire_KeyStore_Interface');
        $keyStore->expects($this->once())->method('exists')->will($this->returnValue(true));
        $keyStore->expects($this->once())->method('delete');

        $notices = $this->notices;

        $notices->setPermissionValidator($permissionValidator);
        $notices->setKeyStore($keyStore);

        $notice = new TestNotice(TestNotice::STATE_HIDDEN, false);
        $notices->setAvailableNotices(array($notice->getName() => $notice));

        $notices->scheduleNotices();
    }

    public function testStateIsAssignedToNotice()
    {
        $notice = new TestNotice();
        $keyStore = $this->getMock('\Interspire_KeyStore_Interface');
        $keyStore->expects($this->once())->method('set')->with('HEADER_NOTICES_TEST:user_1', AbstractNotice::STATE_VISIBLE);

        $permissionValidator = $this->getMock('\ISC_ADMIN_AUTH');
        $permissionValidator->expects($this->once())->method('GetUser')->will($this->returnValue(
            array('pk_userid' => '1')));

        $notices = $this->notices;
        $notices->setPermissionValidator($permissionValidator);
        $notices->setKeyStore($keyStore);
        $notices->setAvailableNotices(array($notice->getName() => $notice));

        $notices->assignStateToNotice(AbstractNotice::STATE_VISIBLE, $notice);
    }

    public function testCreateCreatesAvailableNotice()
    {
        $notices = $this->notices;
        $result = $notices->create(Notices::BILLING);
        $this->assertInstanceOf('\Platform\Notices\BillingNotice', $result);
    }
}

class Mock_Store_Notices1 extends \Store\Notices
{
	protected function getModulesInTestMode($module)
	{
		return true;
	}
}

class Mock_Store_Notices2 extends \Store\Notices
{
	protected function getControlPanelWarningMessage()
	{
		return 'This is a test warning message';
	}
}
