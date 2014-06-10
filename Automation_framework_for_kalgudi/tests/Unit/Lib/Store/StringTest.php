<?php

namespace Unit\Lib\Store;

class StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Store_Settings
     */
    private function getSettings()
    {
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy(array(
            'ShopPath'       => 'http://foo.com',
            'ShopPathNormal' => 'http://foo-normal.com',
            'ShopPathSSL'    => 'https://foo-ssl.com',
        )));
        $settings->load();

        return $settings;
    }

    public function testRemoveShopPathPlaceholderHttpsNull()
    {
        $original = '%%GLOBAL_ShopPath%% %%GLOBAL_ShopPathSSL%%';

        // Assert that ShopPath is replaced with ShopPath and ShopPathSSL is replaced with ShopPathSSL.
        $replaced = \Store_String::removeShopPathPlaceholder($original, null, $this->getSettings());
        $this->assertEquals('http://foo.com https://foo-ssl.com', $replaced);

        // Assert that ShopPath is replaced with ShopPathNormal and ShopPathSSL is replaced with ShopPathSSL.
        $replaced = \Store_String::removeShopPathPlaceholder($original, false, $this->getSettings());
        $this->assertEquals('http://foo-normal.com https://foo-ssl.com', $replaced);

        // Assert that ShopPath is replaced with ShopPathSSL and ShopPathSSL is replaced with ShopPathSSL.
        $replaced = \Store_String::removeShopPathPlaceholder($original, true, $this->getSettings());
        $this->assertEquals('https://foo-ssl.com https://foo-ssl.com', $replaced);
    }
}
