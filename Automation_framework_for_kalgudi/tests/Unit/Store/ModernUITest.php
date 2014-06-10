<?php

class Unit_Store_ModernUI extends PHPUnit_Framework_TestCase
{
    private function getSettings($config)
    {
        $settings = new Store_Settings(new Store_Settings_Driver_Dummy($config));
        $settings->load();
        return $settings;
    }

    public function testControlPanelTitleStoreName()
    {
        $settings = $this->getSettings(array(
            'PlanName'          => 'Test Plan',
            'StoreName'         => 'Test Store',
            'ControlPanelTitle' => 'Title - %%STORE_NAME%%',
        ));

        $title = Store_ModernUI::getControlPanelTitle($settings);

        $this->assertEquals('Title - Test Store', $title);
    }

    public function testControlPanelTitlePlanName()
    {
        $settings = $this->getSettings(array(
            'PlanName'          => 'Test Plan',
            'StoreName'         => 'Test Store',
            'ControlPanelTitle' => 'Title - %%EDITION%%',
        ));

        $title = Store_ModernUI::getControlPanelTitle($settings);

        $this->assertEquals('Title - Test Plan', $title);
    }

    /**
     * Tests the %%STORE_NAME_DASH%% replacement with a non-empty store name.
     */
    public function testControlPanelTitleStoreNameDashNotEmpty()
    {
        $settings = $this->getSettings(array(
            'PlanName'          => 'Test Plan',
            'StoreName'         => 'Test Store',
            'ControlPanelTitle' => '%%STORE_NAME_DASH%% Test',
        ));

        $title = Store_ModernUI::getControlPanelTitle($settings);

        $this->assertEquals('Test Store - Test', $title);
    }

    /**
     * Tests the %%STORE_NAME_DASH%% replacement with an empty store name.
     */
    public function testControlPanelTitleStoreNameDashEmpty()
    {
        $settings = $this->getSettings(array(
            'PlanName'          => 'Test Plan',
            'StoreName'         => '',
            'ControlPanelTitle' => '%%STORE_NAME_DASH%% Test',
        ));

        $title = Store_ModernUI::getControlPanelTitle($settings);

        $this->assertEquals('Test', $title);
    }
}
