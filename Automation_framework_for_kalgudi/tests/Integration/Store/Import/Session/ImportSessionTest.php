<?php

use Store\Import\Session as ImportSession;

abstract class Unit_Lib_Store_Import_ImportSessionTest extends Interspire_IntegrationTest
{

	public function tearDown()
	{
		// delete ALL THE SESSIONS!
		$sessions = ImportSession::getAllSessions();
		foreach ($sessions as $session) {
			$session->delete();
		}
		parent::tearDown();
	}

	public function testCreateDelete()
	{
		$session = new ImportSession('product');
		$id = $session->getId();

		$session->save();
		$this->assertTrue(ImportSession::hasSession($id));

		$session->delete();
		$this->assertFalse(ImportSession::hasSession($id));
	}

	public function testCreateLastModified()
	{
		$session = new ImportSession('product');
		$id = $session->getId();
		$session->save();

		$beforeSaveTime = time();
		$session["lol"] = "cake";
		$session->save();

		$this->assertGreaterThanOrEqual($session->lastModified(), $beforeSaveTime, 'session modified time should be greater than before save time');
		$this->assertGreaterThanOrEqual(time(), $session->lastModified(), 'now should be greater than session modified time');
	}


	public function testCreateAndLoad()
	{
		$session = new ImportSession('product');
		$session->save();
		$id = $session->getId();

		$loaded = ImportSession::getSessionById($id);
		$this->assertNotNull($loaded);
	}

	public function testCreateFillAndLoad()
	{
		$expected = explode(" ", "setUp() and tearDown() are nicely symmetrical in theory but not in practice. In practice, you only need to implement tearDown() if you have allocated external resources like files or sockets in setUp()");
		$session = new ImportSession('product');
		foreach ($expected as $k => $v) {
			$session[$k] = $v;
		}
		$session->save();
		$id = $session->getId();

		$loaded = ImportSession::getSessionById($id);
		foreach ($expected as $k => $v) {
			$this->assertEquals($v, $loaded[$k]);
		}
	}

	public function testCreateFillComplexAndLoad()
	{
		$str = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sem dui, pulvinar eget tincidunt eget, ornare vitae eros. Nulla ante massa, venenatis nec iaculis vel, auctor vel odio. Fusce semper nulla eu metus volutpat iaculis. Phasellus et purus enim. Nunc sit amet justo eu nibh volutpat convallis non ut diam. Integer et lacus vitae nisi egestas imperdiet. Curabitur venenatis magna a orci pulvinar dapibus cursus enim iaculis. Pellentesque hendrerit, nunc a aliquet molestie, est augue vehicula mi, ac consequat quam magna non nulla. Praesent lectus erat, varius vel luctus ac, condimentum eu ligula. Nulla sodales pretium tellus, eu mattis enim ultricies et. Vestibulum dictum sem vel sem consequat consequat.';
		$parts = explode('.', $str);
		$session = new ImportSession('product');
		$expected = array();
		foreach ($parts as $part) {
			$chuncks = explode(' ', $part);
			$expected[] = $chuncks;
			$session[] = $chuncks;
		}
		$session->save();
		$id = $session->getId();

		$loaded = ImportSession::getSessionById($id);
		foreach ($expected as $key => $vals) {
			foreach ($vals as $nkey => $value) {
				$this->assertEquals($value, $loaded[$key][$nkey]);
			}
		}
	}

	public function testCreateAndList()
	{
		$sessions = array();
		for ($i = 0; $i < 10; $i++) {
			$session = new ImportSession('product');
			$session->save();
			$sessions[] = $session->getId();
		}
		$loadedSessions = ImportSession::getAllSessions();
		foreach ($loadedSessions as $loadedSession) {
			$this->assertTrue(in_array($loadedSession->getId(), $sessions));
		}
	}

	public function testCreateAndListByType()
	{
		$types = array("product", "productsku", "trackingnumber", "customer");
		$sessions = array();
		foreach ($types as $type) {
			$sessions[$type] = array();
			for ($i = 0; $i < 10; $i++) {
				$session = new ImportSession($type);
				$session->save();
				$sessions[$type][] = $session->getId();
			}
		}
		foreach ($types as $type) {
			$loadedSessions = ImportSession::getSessionsByType($type);
			$this->assertEquals(count($sessions[$type]), count($loadedSessions));
			foreach ($loadedSessions as $loadedSession) {
				$this->assertTrue(in_array($loadedSession->getId(), $sessions[$type]));
			}
		}

	}

}
