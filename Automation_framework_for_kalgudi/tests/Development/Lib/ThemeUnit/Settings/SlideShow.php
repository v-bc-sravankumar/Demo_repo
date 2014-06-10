<?php

class Unit_Lib_Theme_Settings_SlideShow extends Interspire_UnitTest
{
	/**
	 * @var Theme_Settings_SlideShow
	 */
	private $_slideShow;

	public function setUp()
	{
		parent::setUp();
		$this->_slideShow = new Theme_Settings_SlideShow();
		$this->_slideShow->setName('test-slide-show');
	}

	public function testGetSwapFrequencyEqualsSwapFrequencySet()
	{
		$swapFrequency = 5;
		$this->_slideShow->setSwapFrequency($swapFrequency);
		$this->assertEquals($swapFrequency, $this->_slideShow->getSwapFrequency());
	}

	public function testGetSlidesContainsSlideAdded()
	{
		$slide = $this->getMock('Theme_Settings_Slide');
		$this->_slideShow->addSlide($slide);
		$this->assertContains($slide, $this->_slideShow->getSlides());
	}

	public function testGetSlidesEqualsSlidesSet()
	{
		$slides = $this->_getMockSlides(5);
		$this->_slideShow->setSlides($slides);
		$this->assertEquals($slides, $this->_slideShow->getSlides());
	}

	public function testAddSlideSetsSlideShowId()
	{
		$slide = $this->getMock('Theme_Settings_Slide');
		$slide
			->expects($this->once())
			->method('setSlideShowId')
			->with($this->_slideShow->getId());
		$this->_slideShow->addSlide($slide);
	}

	public function testGetSlidesWithSortSortsSlidesByPosition()
	{
		$slides = $this->_getMockSlides(5);
		$slides = array_merge($slides, $this->_getMockSlides(5, 6));
		$this->_slideShow->setSlides($slides);
		$setSlides = $this->_slideShow->getSlides(true);

		$this->assertSlidesAreSorted($setSlides);
	}

	/**
	 * @param int $count The number of mock slides to create and return.
	 * @param int $firstId The ID to be used for the slide. IDs will increment by one for each slide created.
	 * @param int $firstPosition The position to be used for the first slide. Positions will increment by one for each
	 * slide created.
	 * @return array
	 */
	private function _getMockSlides($count = 1, $firstId = 1, $firstPosition = 1)
	{
		$mockSlides = array();
		$id = $firstId;
		$position = $firstPosition;
		while ($count > 0) {
			$slide = $this->getMock('Theme_Settings_Slide');
			$slide->expects($this->any())->method('getId')->will($this->returnValue($id++));
			$slide->expects($this->any())->method('getPosition')->will($this->returnValue($position++));
			$slide->expects($this->any())->method('getName')->will($this->returnValue("mock-slide-{$id}"));
			$mockSlides[] = $slide;
			$count--;
		}

		return $mockSlides;
	}
	/**
	 * Asserts that the given array of slides is sorted by position.
	 *
	 * @param mixed $slides The slides that should be sorted.
	 * @param string $message
	 * @throws PHPUnit_Framework_AssertionFailedError
	 */
	public function assertSlidesAreSorted($slides, $message = '')
	{
		if (empty($message)) {
			$message = 'Failed asserting that slides were sorted.';
		}
		foreach ($slides as $outerKey => $outerSlide) {
			foreach ($slides as $innerKey => $innerSlide) {
				if ($outerKey >= $innerKey) {
					continue;
				}
				$outerPosition = $outerSlide->getPosition();
				$innerPosition = $innerSlide->getPosition();
				$this->assertLessThanOrEqual($innerPosition, $outerPosition, $message);
			}
		}
	}

}
