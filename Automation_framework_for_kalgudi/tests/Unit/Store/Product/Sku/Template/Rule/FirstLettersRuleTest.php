<?php
use Store\Product\Sku\Template\Rule\FirstLettersRule;

class FirstLettersRuleTest extends PHPUnit_Framework_TestCase
{
    public function testUseDefaultLengthGivenNonPositiveLength()
    {
        $rule = new FirstLettersRule(-1);
        $this->assertEquals(FirstLettersRule::DEFAULT_LENGTH, $rule->getData());

        $rule = new FirstLettersRule(0);
        $this->assertEquals(FirstLettersRule::DEFAULT_LENGTH, $rule->getData());

        $rule = new FirstLettersRule("");
        $this->assertEquals(FirstLettersRule::DEFAULT_LENGTH, $rule->getData());
    }

    /**
     * @expectedException Store\Product\Sku\Template\Rule\Error\InvalidRuleInputException
     */
    public function testApplyNonStringInput()
    {
        $rule = new FirstLettersRule();
        $rule->apply(1);
    }

    public function testApplyShouldReturnAlphaNumericValue()
    {
        $rule = new FirstLettersRule();
        $this->assertEquals("BIG", $rule->apply("  *_* big commerce  "));
    }
}
