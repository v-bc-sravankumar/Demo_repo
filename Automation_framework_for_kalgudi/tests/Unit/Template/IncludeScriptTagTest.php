<?php

class IncludeScriptTagTest extends PHPUnit_Framework_TestCase
{
    public function testEmbedScriptForExistingSource()
    {
        $templateExtension = new Assets\ManifestPaths();

        $scriptTag = $templateExtension->includeScriptTag("profile.js");

        $this->assertContains("text/javascript", $scriptTag);
        $this->assertContains("profile", $scriptTag);
    }

    public function testIgnoreScriptForMissingSource()
    {
        $templateExtension = new Assets\ManifestPaths();

        $scriptTag = $templateExtension->includeScriptTag("missing_source_path.js");

        $this->assertNull($scriptTag);
    }

    public function testEmbedStylesheetForExistingSource()
    {
        // TODO: this needs to come back when integrated apps are brought in
        // $templateExtension = new Assets\ManifestPaths();

        // $linkTag = $templateExtension->includeStylesheetTag("integrated_apps.css");

        // $this->assertContains("stylesheet", $linkTag);
        // $this->assertContains("text/css", $linkTag);
        // $this->assertContains("/assets/css/apps.css", $linkTag);
    }

    public function testIgnoreStylesheetForMissingSource()
    {
        $templateExtension = new Assets\ManifestPaths();

        $linkTag = $templateExtension->includeStylesheetTag("you_dont_understand.css");

        $this->assertNull($linkTag);
    }
}
