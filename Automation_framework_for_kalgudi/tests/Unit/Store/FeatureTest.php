<?php

namespace Unit\Store;

use Store_Feature;
use \bitfield\Bitfield;

class FeatureTest extends \PHPUnit_Framework_TestCase
{
    /** @var Store_Settings */
    protected $_settings;

    protected $features;
    protected $backupSettingsInstance;

    public function setUp()
    {
        $this->backupSettingsInstance = \Store_Config::getInstance();

        $defaults = require ISC_CONFIG_DEFAULT_FILE;

        $this->_settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($defaults));
        $this->_settings->load();

        \Store_Config::setInstance($this->_settings);

        $this->features = Store_Feature::getPlanBasedFeatures();
    }

    public function tearDown()
    {
        \Store_Config::setInstance($this->backupSettingsInstance);
    }

    public function testUpdatingFeatureFlagsByArray()
    {
        $data = new \SimpleXMLElement('
            <feature_flags>
                <CustomerGroups>0</CustomerGroups>
                <CustomerGroupsDiscounts>0</CustomerGroupsDiscounts>
            </feature_flags>
        ');

        Store_Feature::updateFeatures($data);

        $scheduled = $this->_settings->getAllScheduled();

        $key = 'Feature_CustomerGroups';
        $this->assertArrayHasKey($key, $scheduled);
        $this->assertFalse($scheduled[$key]);

        $key = 'Feature_CustomerGroupsDiscounts';
        $this->assertArrayHasKey($key, $scheduled);
        $this->assertFalse($scheduled[$key]);
    }

    public function testUpdatingFeatureFlagsByArrayDoesNotOverrideAvailable()
    {
        // Disable CustomerGroups.
        Store_Feature::disable('CustomerGroups');

        // Now fake an update.
        $data = new \SimpleXMLElement('
            <feature_flags>
                <CustomerGroups>1</CustomerGroups>
            </feature_flags>
        ');
        Store_Feature::updateFeatures($data);

        // Since the feature was already available, nothing should have happened.
        $this->assertFalse(Store_Feature::isEnabled('CustomerGroups'));
    }

    public function testScheduleFeatureFlagsByBitmaskWithAllTurnedOff()
    {
        Store_Feature::updateFeatureFlags(0);

        $scheduled = $this->_settings->getAllScheduled();
        foreach ($this->features as $f) {
            $key = \Store_Feature::getAvailableConfigKey($f);
            if (\Store_Config::exists($key)) {
                $this->assertArrayHasKey($key, $scheduled);
                $this->assertFalse($scheduled[$key]);
            }

            $key = \Store_Feature::getConfigKey($f);
            if (\Store_Config::exists($key)) {
                $this->assertArrayHasKey($key, $scheduled);
                $this->assertFalse($scheduled[$key]);
            }
        }
    }

    public function testUpdateFeatureFlagsByBitmaskDoesntEnableWhenItShouldNot()
    {
        // turn all features on and commit config
        Store_Feature::updateFeatureFlags(7, true);

        // disbale a feature
        Store_Feature::disable('AbandonedCartNotifications');

        // turn all features on again -- this mimicks the refresh plan scenario for a gold plan store amongst others
        Store_Feature::updateFeatureFlags(7, true);

        // check that feature is still off
        $this->assertFalse(Store_Feature::isEnabled('AbandonedCartNotifications'));
    }

    public function testCommitFeatureFlagsByBitmaskWithAllTurnedOff()
    {
        Store_Feature::updateFeatureFlags(0, true);

        foreach ($this->features as $f) {
            if (\Store_Config::exists('FeatureAvailable_'.$f)) {
                $this->assertFalse(Store_Feature::isAvailable($f));
            }

            if (\Store_Config::exists('Feature_'.$f)) {
                $this->assertFalse(Store_Feature::isEnabled($f));
            }
        }
    }

    public function testMakeAvailableScheduleOnly()
    {
        $f = 'AbandonedCartNotifications';

        Store_Feature::makeAvailable($f, false);
        $this->assertTrue(\Store_Config::isChanging("FeatureAvailable_$f"));

        $scheduled = $this->_settings->getAllScheduled();
        $this->assertTrue($scheduled["FeatureAvailable_$f"]);
    }

    public function testMakeAvailableAndCommit()
    {
        Store_Feature::makeAvailable('AbandonedCartNotifications', true);
        $this->assertTrue(\Store_Feature::isAvailable('AbandonedCartNotifications'));
    }

    public function testMakeUnAvailableScheduleOnly()
    {
        $f = 'AbandonedCartNotifications';

        Store_Feature::makeUnAvailable($f, false);
        $this->assertTrue(\Store_Config::isChanging("FeatureAvailable_$f"));

        $scheduled = $this->_settings->getAllScheduled();
        $this->assertFalse($scheduled["FeatureAvailable_$f"]);
    }

    public function testMakeUnAvailableAndCommit()
    {
        Store_Feature::makeUnAvailable('AbandonedCartNotifications', true);
        $this->assertFalse(\Store_Feature::isAvailable('AbandonedCartNotifications'));
    }

    public function testEnableScheduleOnly()
    {
        $f = 'AbandonedCartNotifications';

        Store_Feature::enable($f, false);
        $this->assertTrue(\Store_Config::isChanging("Feature_$f"));

        $scheduled = $this->_settings->getAllScheduled();
        $this->assertTrue($scheduled["Feature_$f"]);
    }

    public function testEnableAndCommit()
    {
        Store_Feature::enable('AbandonedCartNotifications', true);
        $this->assertTrue(\Store_Feature::isEnabled('AbandonedCartNotifications'));
    }

    public function testdisableScheduleOnly()
    {
        $f = 'AbandonedCartNotifications';

        Store_Feature::disable($f, false);
        $this->assertTrue(\Store_Config::isChanging("Feature_$f"));

        $scheduled = $this->_settings->getAllScheduled();
        $this->assertFalse($scheduled["Feature_$f"]);
    }

    public function testdisableAndCommit()
    {
        Store_Feature::disable('AbandonedCartNotifications', true);
        $this->assertFalse(\Store_Feature::isEnabled('AbandonedCartNotifications'));
    }

    /**
     * Convenience method for turning a 'hash' of fields into a suitable array.
     * @param array $fields A 2D array of field names and values.
     * @return array
     */
    private function createTestArray($fields)
    {
        $data = array();

        foreach ($fields as $field) {
            $row = new \stdClass();
            $row->name = $field['name'];
            $row->enabled = $field['enabled'];
            $data[] = $row;
        }

        return $data;
    }
}
