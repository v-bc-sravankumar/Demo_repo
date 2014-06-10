<?php

require_once __DIR__.'/../../../../lib/general.php';

class Unit_Store_DesignTest extends PHPUnit_Framework_TestCase
{
    public function testEnableThemeFeaturesWithExistingCarousel()
    {
        // Make sure we start with a store with Theme_EnabledFeatures = 0 (FEATURE_BASE).
        StoreConfigTest::schedule('template', 'Classic');
        StoreConfigTest::schedule('Theme_EnabledFeatures', Theme::FEATURE_BASE);

        $layout = new AdminLayoutTest();
        $layout->enableThemeFeatures(Theme::FEATURE_SET_MODERNUI);

        $this->assertEquals(StoreConfigTest::get('Theme_EnabledFeatures'), Theme::FEATURE_SET_MODERNUI);
    }

    public function testEnableThemeFeaturesWithoutExistingCarousel()
    {
        // Change to a theme without an existing carousel.
        StoreConfigTest::schedule('template', 'Adventure');
        StoreConfigTest::schedule('Theme_EnabledFeatures', Theme::FEATURE_BASE);

        $mock = $this
            ->getMockBuilder('Theme_Settings_SlideShow')
            ->setMethods(array('setTheme', 'save'))
            ->getMock();

        $mock
            ->expects($this->at(0))
            ->method('setTheme')
            ->with($this->equalTo('Adventure'));

        $mock
            ->expects($this->at(1))
            ->method('save');

        ThemeSettingsSlideShowTest::$mock = $mock;

        $layout = new AdminLayoutTest();
        $layout->enableThemeFeatures(Theme::FEATURE_SET_MODERNUI);

        $this->assertEquals(StoreConfigTest::get('Theme_EnabledFeatures'), Theme::FEATURE_SET_MODERNUI);
    }

	public function testThemeSupports()
	{
		$this->assertTrue(\Theme::themeSupports('Classic', \Theme::FEATURE_SET_MODERNUI));
		$this->assertFalse(\Theme::themeSupports('Adventure', \Theme::FEATURE_SET_MODERNUI));
	}

    public function testCurrentThemeSupports()
    {
        // Test that this works for a theme with 'Features' set to FEATURE_SET_MODERNUI and Theme_EnabledFeatures set to
        // FEATURE_BASE.
        Store_Config::override('template', 'Classic');
        Store_Config::override('Theme_EnabledFeatures', Theme::FEATURE_BASE);
        $this->assertTrue(Theme::currentThemeSupports(Theme::FEATURE_SET_MODERNUI, 'StoreConfigTest'));

        // Test that this works for a theme with 'Features' set to FEATURE_SET_MODERNUI and 'Theme_EnabledFeatures' set
        // to 'FEATURE_SET_MODERNUI'.
        Store_Config::override('template', 'Classic');
        Store_Config::override('Theme_EnabledFeatures', Theme::FEATURE_SET_MODERNUI);

        $this->assertTrue(Theme::currentThemeSupports(Theme::FEATURE_SET_MODERNUI, 'StoreConfigTest'));

        // Test for a theme with 'Features' not set and 'Theme_EnabledFeatures' set to 'FEATURE_SET_MODERNUI'.
        Store_Config::override('Theme_EnabledFeatures', Theme::FEATURE_SET_MODERNUI);
        Store_Config::override('template', 'default');

        $this->assertTrue(Theme::currentThemeSupports(Theme::FEATURE_SET_MODERNUI, 'StoreConfigTest'));

        // Test for a theme with 'Features' not set and with 'Theme_EnabledFeatures' set to 'FEATURE_BASE.'
        Store_Config::override('Theme_EnabledFeatures', Theme::FEATURE_BASE);
        Store_Config::override('template', 'Adventure');

        $this->assertFalse(Theme::currentThemeSupports(Theme::FEATURE_SET_MODERNUI, 'StoreConfigTest'));
    }
}

class AdminLayoutTest extends ISC_ADMIN_LAYOUT
{
    public function __construct()
    {
        // Do nothing.
    }

    public function enableThemeFeatures($features, $flashMessage = true, $storeConfig = 'Store_Config', $themeSettingsSlideShow = 'Theme_Settings_SlideShow')
    {
        // Second parameter indicates that we're running this from inside a test.
        parent::enableThemeFeatures($features, false, 'StoreConfigTest', 'ThemeSettingsSlideShowTest');
    }
}

class StoreConfigTest
{
    private static $_values = array();

    public static function get($key)
    {
        return self::$_values[$key];
    }

    public static function schedule($key, $value)
    {
		self::$_values[$key] = $value;
    }

    public static function commit()
    {
    }
}

class ThemeSettingsSlideShowTest
{
    public static $mock;

    public static function findByTheme($theme)
    {
        if ($theme === 'Classic') {
            $retval = new SlideShowResult();
            $retval->value = new stdClass();

            return $retval;
        }

        $retval = new SlideShowResult();
        $retval->value = false;

        return $retval;
    }

    public static function find()
    {
        $retval = new SlideShowResult();
        $retval->value = self::$mock;

        return $retval;
    }
}

class SlideShowResult
{
    public $value;

    public function first()
    {
        return $this->value;
    }
}

