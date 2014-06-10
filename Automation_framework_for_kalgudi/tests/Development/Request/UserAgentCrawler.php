<?php
class UserAgentCrawler extends PHPUnit_Framework_TestCase
{

	protected static $userAgents = array();

	/**
	 * Get the dataset from http://www.user-agents.org
	 */
	public static function setUpBeforeClass()
	{
		$xml = file_get_contents('http://www.user-agents.org/allagents.xml');
		self::$userAgents = Interspire_Xml::xml2array(new SimpleXMLElement($xml), 'user-agent');
	}

	/**
	 * Run the sample against the detect function we have
	 */
	public function test()
	{
		$agents = &self::$userAgents;

		$leakedCount = 0;
		$falsePositiveCount = 0;
		$sampleSize = 0;
		$leaked = array();
		$falsePositive = array();

		foreach ($agents as $agent) {

			$userAgent = $agent['String'];
			$request = new Interspire_Request(null, null, null, array('HTTP_USER_AGENT' => $userAgent));
			$type = (is_array($agent['Type']) ? implode('', $agent['Type']) : $agent['Type']);
			$isBrowser = (isc_strtoupper($type) == 'B');
			$detectedAsCrawler = $request->isCrawlerAgent();

			if (!$detectedAsCrawler && !$isBrowser) {
				// leaked
				$leaked[] = $userAgent;
				$leakedCount++;
			} else if ($detectedAsCrawler && $isBrowser) {
				// false positive
				$falsePositive[] = $userAgent;
				$falsePositiveCount++;
			}

			if (!$isBrowser) {
				$sampleSize++;
			}
		}

		$blocked = $sampleSize - $leakedCount;

		// Check that we have detected more than 65% of crawlers hitting us
		$this->assertEquals(true, ($blocked/$sampleSize) > 0.65);

	}

}