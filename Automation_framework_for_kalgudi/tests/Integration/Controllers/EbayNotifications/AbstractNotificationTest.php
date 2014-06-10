<?php

namespace Integration\Controllers\EbayNotifications;

use EbayNotificationsController;
use Interspire_Request;
use Store_Settings;
use Store_Settings_Driver_Dummy;
use PHPUnit_Framework_TestCase;

abstract class AbstractNotificationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Gets a configured ebay notifications controller.
     *
     * @param Interspire_Request $request
     * @return EbayNotificationsController
     */
    protected function getController(Interspire_Request $request)
    {
        $settings = new Store_Settings(new Store_Settings_Driver_Dummy(array(
            'EbayDevId' => 'devId',
            'EbayAppId' => 'appId',
            'EbayCertId' => 'certId',
            'EbayUserToken' => 'token',
            'EbaySettingsValid' => true,
        )));
        $settings->load();

        $controller = new EbayNotificationsController($settings);
        $controller->setRequest($request);
        $controller->setResponse($request->getResponse());

        return $controller;
    }

    /**
     * Loads an XML fixture from the /fixtures folder.
     *
     * @param string $fixtureName Name of the fixture.
     * @param array $replacements Array of values to replace into the fixture.
     * @return string
     */
    protected function getFixture($fixtureName, $replacements = array())
    {
        $fixture = file_get_contents(__DIR__ . '/fixtures/' . $fixtureName . '.xml');

        $replacements = array_merge($replacements, $this->getCommonVariables());

        foreach ($replacements as $var => $value) {
            $fixture = str_replace("%%$var%%", $value, $fixture);
        }

        return $fixture;
    }

    /**
     * Gets common variables to use in the fixtures.
     * - Signature
     * - Timestamp
     *
     * @return array
     */
    protected function getCommonVariables()
    {
        $timestamp = '2013-08-26T00:48:15.000Z';

        return array(
            'Signature' => base64_encode(md5($timestamp . 'devIdappIdcertId', true)),
            'Timestamp' => $timestamp,
        );
    }

    /**
     * Posts an ebay notification into the EbayNotificationsController.
     *
     * @param string $action The SOAP action of the notification.
     * @param string $fixtureName Name of the fixture to post into the controller.
     * @param array $replacements Optional array of values to replace into the fixture.
     * @return mixed Return value of the handled notification.
     */
    protected function postNotification($action, $fixtureName, $replacements = array())
    {
        $server = array(
            'HTTP_SOAPACTION' => $action,
        );

        $notification = $this->getFixture($fixtureName, $replacements);

        $request = new Interspire_Request(null, null, null, $server, $notification);
        $controller = $this->getController($request);

        return $controller->postAction();
    }
}
