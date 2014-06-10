<?php
use Store\Product\Sku\Template\Rule\LastLettersRule;

class LastLettersRuleTest extends PHPUnit_Framework_TestCase
{
    public function testUseDefaultLengthGivenNonPositiveLength()
    {
        $rule = new LastLettersRule(-1);
        $this->assertEquals(LastLettersRule::DEFAULT_LENGTH, $rule->getData());

        $rule = new LastLettersRule(0);
        $this->assertEquals(LastLettersRule::DEFAULT_LENGTH, $rule->getData());

        $rule = new LastLettersRule("");
        $this->assertEquals(LastLettersRule::DEFAULT_LENGTH, $rule->getData());
    }

    /**
     * @expectedException Store\Product\Sku\Template\Rule\Error\InvalidRuleInputException
     */
    public function testApplyNonStringInput()
    {
        $rule = new LastLettersRule();
        $rule->apply(1);
    }

    public function testApplyShouldReturnAlphaNumericValue()
    {
        $rule = new LastLettersRule();
        $this->assertEquals("RCE", $rule->apply("  *_* big commerce  "));
    }
}
