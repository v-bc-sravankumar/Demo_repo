<?php

require_once __DIR__.'/../../../lib/templates/panel.php';
require_once __DIR__.'/../../../includes/display/HomeSlideShow.php';

class Unit_App_ThemeSlideTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_data = array(
            'id' => 12,
            'name' => 'some-slide-show',
            'theme' => 'some-theme',
            'position' => 0,
            'theme_image_id' => 34,
            'theme_slide_show_id' => 1234,
            'link_url' => 'http://google.com',
            'link_type' => 'external',
            'link_id' => 56,
            'heading_text' => 'heading-text',
            'heading_colour' => '#ffffff',
            'text_text' => 'text-text',
            'text_colour' => '#000000',
            'button_text' => 'button-text',
            'button_colour' => '#777777'
        );

    }

    public function testJsonSerializeNonExistentImage()
    {
        $data = $this->_data;

        $slideMock = $this
            ->getMock(
                'Theme_Settings_Slide', // class to mock
                array('getImage'),      // methods to mock
                array($data),           // constructor arguments
                '',                     // mock class name (use default)
                true                    // call original constructor
            );

        // Test that jsonSerialize does not fail when `getImage' returns `null'.
        // (`getImage' returns `null' when the image does not exist in the `theme_images'
        // table in the db.)
        $slideMock
            ->expects($this->at(0))
            ->method('getImage')
            ->will($this->returnValue(null));

        $jsonData = $slideMock->jsonSerialize();

        $this->assertEquals($data['id'], $jsonData->id);
        $this->assertEquals($data['name'], $jsonData->name);
        $this->assertEquals($data['theme'], $jsonData->theme);
        $this->assertEquals($data['position'], $jsonData->position);
        $this->assertEquals($data['link_url'], $jsonData->link->url);
        $this->assertEquals($data['link_type'], $jsonData->link->type);
        $this->assertEquals($data['button_text'], $jsonData->button->text);
        $this->assertEquals($data['button_colour'], $jsonData->button->colour);
        $this->assertEquals($data['text_text'], $jsonData->text->text);
        $this->assertEquals($data['text_colour'], $jsonData->text->colour);
        $this->assertEquals($data['heading_text'], $jsonData->heading->text);
        $this->assertEquals($data['heading_colour'], $jsonData->heading->colour);
        $this->assertEquals(null, $jsonData->image);
    }

}

class TestHomeSlideShowPanel extends ISC_HomeSlideShow_PANEL
{
    public function __construct() { } // disable original constructor

    public function generatePanelOutput() {
        return $this->_generatePanelOutput();
    }
}

class TestSlideShow
{
    private $_slides;

    public function __construct($slides) {
        $this->_slides = $slides;
    }

    public function getSlides($sorted = false)
    {
        return $this->_slides;
    }
}

