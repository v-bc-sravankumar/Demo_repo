<?php

class Unit_Store_LocaleTest extends PHPUnit_Framework_TestCase
{
	public function testCanParseAcceptsLanguagesInOrder()
	{
		$langs = "en,en-US;q=0.7,en-GB;q=0.5,de;q=0.3";

		$sortLangs = Store_Locale::getLocalesInOrder($langs);
		$this->assertEquals($sortLangs,array("en","en-US","en-GB","de"));
	}

	public function testCanParseAcceptsLanguagesOutOfOrder()
	{
		$langs = "en-US;q=0.4,en,en-GB;q=0.2,de;q=0.1";

		$sortLangs = Store_Locale::getLocalesInOrder($langs);
		$this->assertEquals($sortLangs,array("en","en-US","en-GB","de"));
	}

	public function testCanParseAcceptsLanguagesSingle()
	{
		$langs = "en";

		$sortLangs = Store_Locale::getLocalesInOrder($langs);
		$this->assertEquals($sortLangs,array("en"));
	}

	public function testGetBestLanguageReturnDefault()
	{
		$locConfig = Store_Locale::getSupportedLanguages();
		$langs = "en";

		$bestLang = Store_Locale::getBestLocale($langs);

		$this->assertEquals($bestLang,$locConfig["default"]);
	}

	public function testLanguagesAreNormalized()
	{
		$langs = "en_US";
		$bestLang = Store_Locale::getBestLocale($langs);
		$this->assertEquals($bestLang, "en-US");
	}

	public function testGetBestLanguageReturnAllowed()
	{
		$locConfig = Store_Locale::getSupportedLanguages();
		$langs = "en-GB";

		$bestLang = Store_Locale::getBestLocale($langs);

		$this->assertEquals($bestLang,"en-GB");
	}

	public function testGetBestLanguageReturnPriorityUnavailable()
	{
		// If we ever support the Amharic language, this test will fail. I'm thinking that's pretty safe.
		$langs = "am,en-GB;q=0.6,en-US;q=0.5";

		$bestLang = Store_Locale::getBestLocale($langs);

		$this->assertEquals($bestLang,"en-GB");
	}

	public function testGetBestLanguageReturnPriorityAvailable()
	{
		$locConfig = Store_Locale::getSupportedLanguages();
		$langs = "en-GB,de;q=0.6,en-US;q=0.5";

		$bestLang = Store_Locale::getBestLocale($langs);

		$this->assertEquals($bestLang,"en-GB");
	}

	public function testGetBestLanguageReturnHigherPriority()
	{
		$locConfig = Store_Locale::getSupportedLanguages();
		$langs = "en-GB;q=0.6,en-US;q=1.0";

		$bestLang = Store_Locale::getBestLocale($langs);

		$this->assertEquals($bestLang,"en-US");
	}
}