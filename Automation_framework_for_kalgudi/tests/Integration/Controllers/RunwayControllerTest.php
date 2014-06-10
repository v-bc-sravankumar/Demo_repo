<?php
use Interspire_Response as Response;
use Playbook\DisplayManager;

class Unit_Controllers_RunwayControllerTest extends PHPUnit_Framework_TestCase
{

    public function testDecliningEnoughTimesDisablesRunway() {

        $displayManager = $this->getMock('Playbook\DisplayManager', array('getDeclineCount', 'permanentlyDecline'));
        $displayManager->expects($this->any())->method('getDeclineCount')->will($this->returnValue( DisplayManager::MAX_DECLINES ));
        $displayManager->expects($this->once())->method('permanentlyDecline');

        $controller = new RunwayController($displayManager);

        $request = new Interspire_Request($get, null, null, array (
            'REQUEST_METHOD' => 'POST',
            'HTTP_ACCEPT' => 'application/json'
        ), "");

        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        $response = $controller->declineAction();

        $this->assertEquals(Response::STATUS_OK, $request->getResponse()->getStatus());

    }

    public function testInvalidExperienceLevelReturnsError()
    {
        $controller = new RunwayController($displayManager);

        $chunk = array(
            'experience_level' => 'invalid'
        );
        $body = json_encode($chunk);

        $request = new Interspire_Request($get, null, null, array (
            'REQUEST_METHOD' => 'POST',
            'HTTP_ACCEPT' => 'application/json'
        ), $body);

        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        $response = $controller->acceptAction();

        $this->assertEquals(Response::STATUS_BAD_REQUEST, $request->getResponse()->getStatus());

    }

    public function testAcceptingRecordsTheTime()
    {

        $displayManager = $this->getMock('Playbook\DisplayManager', array('setAcceptedDate'));
        $displayManager->expects($this->exactly(1))->method('setAcceptedDate');
        $controller = new RunwayController($displayManager);

        $chunk = array(
            'experience_level' => 'new'
        );
        $body = json_encode($chunk);

        $request = new Interspire_Request($get, null, null, array (
            'REQUEST_METHOD' => 'POST',
            'HTTP_ACCEPT' => 'application/json'
        ), $body);

        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        $response = $controller->acceptAction();

        $this->assertEquals(Response::STATUS_OK, $request->getResponse()->getStatus());

    }

    public function testAcceptingRecordsTheSuppliedExperienceLevel()
    {
        $chunk = array(
            'experience_level' => 'new'
        );
        $body = json_encode($chunk);

        $displayManager = $this->getMock('Playbook\DisplayManager', array('setExperienceLevel'));
        $displayManager->expects($this->exactly(1))->method('setExperienceLevel')->with($chunk['experience_level']);
        $controller = new RunwayController($displayManager);

        $request = new Interspire_Request($get, null, null, array (
            'REQUEST_METHOD' => 'POST',
            'HTTP_ACCEPT' => 'application/json'
        ), $body);

        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        $response = $controller->acceptAction();

        $this->assertEquals(Response::STATUS_OK, $request->getResponse()->getStatus());
    }

    public function testGetSettingsReturnsExpectedKeys()
    {

        $settings = array(
            'seen_intro' => false,
            'experience_level' => 'new',
        );

        $displayManager = $this->getMock('Playbook\DisplayManager', array('getSettings'));
        $displayManager->expects($this->exactly(1))->method('getSettings')->will($this->returnValue($settings));
        $controller = new RunwayController($displayManager);

        $request = new Interspire_Request($get, null, null, array (
            'REQUEST_METHOD' => 'GET',
            'HTTP_ACCEPT' => 'application/json'
        ), "");

        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        $expected = array('seen_intro', 'experience_level');
        $actual = $controller->settingsAction();

        $this->assertEquals($expected, array_keys($actual));

    }

    /**
     * @dataProvider experienceLevels
     * @param unknown $experienceLevel
     * @param unknown $responseCode
     */
    public function testExperienceLevelValidation($body, $statusCode, $times)
    {

        $bodyJson = json_encode($body);

        $displayManager = $this->getMock('Playbook\DisplayManager', array('setExperienceLevel', 'setSeenIntro'));
        if((int)$times > 0) {
            $displayManager->expects($this->exactly($times))->method('setExperienceLevel')->with($body['experience_level']);
            $displayManager->expects($this->exactly($times))->method('setSeenIntro')->with($body['seen_intro']);
        } else {
            $displayManager->expects($this->never())->method('setExperienceLevel');
            $displayManager->expects($this->never())->method('setSeenIntro');
        }

        $controller = new RunwayController($displayManager);

        $request = new Interspire_Request(null, null, null, array (
            'REQUEST_METHOD' => 'POST',
            'HTTP_ACCEPT' => 'application/json'
        ), $bodyJson);

        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        $response = $controller->settingsAction();

        $this->assertEquals($statusCode, $request->getResponse()->getStatus());
        if((int)$times) {
            $this->assertEquals(array_keys($body), array_keys($response));
        }

    }

    public function experienceLevels()
    {
        return array(
        	array(array('seen_intro' => false, 'experience_level' => 'new'), 200, 1),
            array(array('seen_intro' => true, 'experience_level' => 'intermediate'), 200, 1),
            array(array('seen_intro' => false, 'experience_level' => 'experienced'), 200, 1),
            array(array('seen_intro' => true, 'experience_level' => 'invalid'), 400, 0),
        );
    }

}
