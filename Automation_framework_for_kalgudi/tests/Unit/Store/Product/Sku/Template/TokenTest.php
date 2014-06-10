<?php
use Store\Product\Sku\Template\Token;
use Store\Product\Sku\Template\Rule\AbstractRule;
use Store\Product\Sku\Template\Rule\AbbreviationRule;
use Store\Product\Sku\Template\Rule\OptionMapRule;

class TokenTest extends PHPUnit_Framework_TestCase
{

    public function testIsValidType()
    {
        $this->assertFalse(Token::isValidType('notvalid'));
        $this->assertTrue(Token::isValidType(Token::TYPE_BRAND));
        $this->assertTrue(Token::isValidType(Token::TYPE_PRODUCT));
        $this->assertTrue(Token::isValidType(Token::TYPE_FIXED));
        $this->assertTrue(Token::isValidType(Token::TYPE_UNIQUE));
        $this->assertTrue(Token::isValidType(Token::TYPE_OPTION));
    }

    /**
     * @expectedException Store\Product\Sku\Template\Error\InvalidTokenTypeException
     */
    public function testConstructUsingInvalidType()
    {
        new Token('notvalid');
    }

    /**
     * @expectedException Store\Product\Sku\Template\Error\InvalidTokenRuleException
     */
    public function testFixedTokenTypeMustMatchFixedValueRule()
    {
        $token = new Token(Token::TYPE_FIXED);
        $token->setRule(new AbbreviationRule());
    }

    /**
     * @expectedException Store\Product\Sku\Template\Error\InvalidTokenRuleException
     */
    public function testOptionMapRuleMustMatchOptionTokenType()
    {
        $token = new Token(Token::TYPE_FIXED);
        $token->setRule(new OptionMapRule(array()));
    }

    /**
     * @expectedException Store\Product\Sku\Template\Error\InvalidTokenTypeException
     */
    public function testLoadFromArrayMissingType()
    {
        Token::loadFromArray(array(
            'rule' => array(
                'type' => AbstractRule::TYPE_ABBR,
                'data' => null,
            ),
        ));
    }

    /**
     * @expectedException Store\Product\Sku\Template\Error\InvalidTokenRuleException
     */
    public function testLoadFromArrayMissingRule()
    {
        Token::loadFromArray(array('type' => Token::TYPE_PRODUCT));
    }
}
