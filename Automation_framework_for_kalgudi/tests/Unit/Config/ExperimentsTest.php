<?php

namespace Unit\Config;

use Config\Experiment;
use Config\Experiments\Subjects\VariableIdentity;
use Abacus\Selectors\FixedSelector;
use Abacus\Selectors\WeightedPercentSelector;
use Abacus\Selectors\HashRandomizer;

class ExperimentsTest extends \PHPUnit_Framework_TestCase
{
	public function testDefineWithFixedSelector()
	{
		Experiment::addSelector("always_variant_1", new \Abacus\Selectors\FixedSelector("variant_1"));

		Experiment::define(array(
		    "id" => "fixed_selector_test",
		    "selector" => "always_variant_1",
		    "variants" => array(
		        "variant_1",
		    ),
		));

		$variant = Experiment::select("fixed_selector_test");

		$this->assertEquals("variant_1", $variant);
		$this->assertInstanceOf("\\Abacus\\Variant", $variant);
	}

	public function testDefineWithWeightedPercentage()
	{
		Experiment::addSelector("weighted_percentage_10%", new WeightedPercentSelector(
			array(
				'variant_1' => 10,
				'variant_2' => 90,
			),
			new HashRandomizer("weighed_selector_test")
		));

		Experiment::define(array(
		    "id" => "weighed_selector_test",
		    "selector" => "weighted_percentage_10%",
		    "variants" => array(
		        "variant_1",
		        "variant_2",
		    ),
		    "subject" => new VariableIdentity(91243342242543)
		));

		$variant1 = Experiment::select("weighed_selector_test");

		$this->assertEquals("variant_1", $variant1);
		$this->assertInstanceOf("\\Abacus\\Variant", $variant1);

		$variant = Experiment::get("weighed_selector_test")->select(new VariableIdentity(123));

		$this->assertEquals("variant_2", $variant);
		$this->assertInstanceOf("\\Abacus\\Variant", $variant);
	}
}