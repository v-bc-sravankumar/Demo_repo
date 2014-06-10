<?php

namespace Unit\Interspire;

use Interspire_TimerStack;

class TimerStackTest extends \PHPUnit_Framework_TestCase
{
	public function testEmptyDepth()
	{
		$begin_depth = Interspire_TimerStack::depth();

		$this->assertEquals(0, $begin_depth, 'the starting depth should be 0 - if this assertion fails, something else is starting a timer outside the test and should be investigated');
		$this->assertFalse(Interspire_TimerStack::stop(), 'stop() should return false when no timers are active');
		$this->assertFalse(Interspire_TimerStack::elapsed(), 'elapsed() should return false when no timers are active');

		return $begin_depth;
	}

	/**
	 * @depends testEmptyDepth
	 */
	public function testStartDepth($begin_depth)
	{
		$stack_first = Interspire_TimerStack::start();
		$stack_second = Interspire_TimerStack::start();

		$this->assertEquals($begin_depth, $stack_first, 'stack_first should be equal to the starting stack');

		usleep(100000);
		$this->assertEquals($begin_depth + 1, $stack_second, 'stack_second should be 1 higher than the starting stack');

		return array($begin_depth, $stack_first, $stack_second);
	}

	/**
	 * @depends testStartDepth
	 */
	public function testElapsedTime($depths)
	{
		$begin_depth = $depths[0];
		$stack_first = $depths[1];
		$stack_second = $depths[2];

		usleep(100000);
		$elapsed_current = Interspire_TimerStack::elapsed();
		$elapsed_second = Interspire_TimerStack::elapsed($stack_second);
		$elapsed_first = Interspire_TimerStack::elapsed($stack_first);

		$this->assertGreaterThanOrEqual($elapsed_current, $elapsed_second, 'the result of elapsed() and elapsed($stack_second) should be roughly the same, give or take a few microseconds'); // using a >= check because of microtime() results... the time between the two elapsed() calls could be slightly different even though they'll reference the same timer
		$this->assertGreaterThan($elapsed_second, $elapsed_first, 'the current elapsed time of the first timer should be longer than the second timer');

		$depths[] = $elapsed_current;
		$depths[] = $elapsed_second;
		$depths[] = $elapsed_first;
		return $depths;
	}

	/**
	 * @depends testElapsedTime
	 */
	public function testStopTimer($depths)
	{
		$begin_depth = $depths[0];
		$stack_first = $depths[1];
		$stack_second = $depths[2];
		$elapsed_current = $depths[3];
		$elapsed_second = $depths[4];
		$elapsed_first = $depths[5];

		usleep(100000);
		$duration_second = Interspire_TimerStack::stop();
		$this->assertGreaterThan($elapsed_second, $duration_second, 'the final duration of the second timer should be greater than its earlier elapsed() call');

		usleep(100000);
		$duration_first = Interspire_TimerStack::stop();
		$this->assertGreaterThan($elapsed_first, $duration_first, 'the final duration of the first timer shoul be greater than its earlier elapsed() call');

		$this->assertGreaterThan($duration_second, $duration_first, 'the final duration of the first timer should be greater than the second timer');

		$this->assertEquals($begin_depth, Interspire_TimerStack::depth(), 'the final stack depth should be the same as the starting stack depth');
		$this->assertFalse(Interspire_TimerStack::stop(), 'stop() should return false when no timers are active');
		$this->assertFalse(Interspire_TimerStack::elapsed(), 'elapsed() should return false when no timers are active');
	}

	public function testElapsedTimeAsMilliseconds()
	{
		Interspire_TimerStack::start();
		usleep(25000);
		$elapsed = Interspire_TimerStack::elapsed(null, true);
		$this->assertEquals(25, $elapsed, '', 10.0);

		usleep(60000);
		$elapsed = Interspire_TimerStack::stop(true);
		$this->assertEquals(85, $elapsed, '', 10.0);

	}

	public function testStopTimeAsMilliseconds()
	{
		Interspire_TimerStack::start();
		usleep(34000);
		$elapsed = Interspire_TimerStack::stop(true);
		$this->assertEquals(34, $elapsed, '', 10.0);
	}
}
