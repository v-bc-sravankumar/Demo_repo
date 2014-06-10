<?php
namespace Action;

class ContentNegotiatorTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_VIEW_CLASS = 'Action\ContentNegotiatorTest_DummyView'; // see after class definition

    public function testMatchesFirstAcceptType()
    {
        $negotiator = new ContentNegotiator(
            array(
                'text/html',
                'application/json',
            ),
            array(
                'application/json' => 'Not\Gonna\Run',
                'text/html' => static::DUMMY_VIEW_CLASS,
            )
        );
        $this->assertTrue($negotiator->canMatchAcceptType());
        $this->assertInstanceOf(static::DUMMY_VIEW_CLASS, $negotiator->createView());
    }

    public function testWithFallback()
    {
        $negotiator = new ContentNegotiator(
            array(
                'application/json'
            ),
            array(
                'text/html' => 'Not\Gonna\Run',
                '*/*' => static::DUMMY_VIEW_CLASS,
            )
        );
        $this->assertTrue($negotiator->canMatchAcceptType());
        $this->assertInstanceOf(static::DUMMY_VIEW_CLASS, $negotiator->createView());
    }

    /**
     * @expectedException \DomainException
     */
    public function testWithoutFallback()
    {
        $negotiator = new ContentNegotiator(
            array(
                'application/json'
            ),
            array(
                'text/html' => 'Not\Gonna\Run',
            )
        );
        $this->assertFalse($negotiator->canMatchAcceptType());
        $negotiator->createView(); // throws exception; see method comment.
    }
}

class ContentNegotiatorTest_DummyView
{
    // Nothing to see here.
}
