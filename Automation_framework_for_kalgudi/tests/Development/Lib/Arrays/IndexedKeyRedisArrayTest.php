<?php

require_once('ArrayObjectTest.php');

use RedisArray\IndexedKeysRedisArray;

class IndexedKeyRedisArrayTest extends ArrayObjectTest
{

    const ARRAY_NAMESPACE = '__RedisArrayTests::array__';

	public function createArray()
	{
		$credis = new \Predis\Client();
		$credis->select(10);
		return new IndexedKeysRedisArray(self::ARRAY_NAMESPACE, $credis);
	}

    public function testExpire()
    {
        $str = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sem dui, pulvinar eget tincidunt eget, ornare vitae eros. Nulla ante massa, venenatis nec iaculis vel, auctor vel odio. Fusce semper nulla eu metus volutpat iaculis. Phasellus et purus enim. Nunc sit amet justo eu nibh volutpat convallis non ut diam. Integer et lacus vitae nisi egestas imperdiet. Curabitur venenatis magna a orci pulvinar dapibus cursus enim iaculis. Pellentesque hendrerit, nunc a aliquet molestie, est augue vehicula mi, ac consequat quam magna non nulla. Praesent lectus erat, varius vel luctus ac, condimentum eu ligula. Nulla sodales pretium tellus, eu mattis enim ultricies et. Vestibulum dictum sem vel sem consequat consequat.';
        $parts = explode('.', $str);
        $expected = array();
        foreach ($parts as $part) {
            $chuncks = explode(' ', $part);
            $expected[] = $chuncks;
            $this->array[] = $chuncks;
        }

        $this->array->expire(10);

        foreach ($this->array as $key => $vals) {
            foreach ($vals as $k => $v) {
                $nestedKey = $this->getValueKey($key . IndexedKeysRedisArray::NESTED_KEY_DELIMITER . $k);
                $this->assertTrue($this->array->getRedis()->ttl($nestedKey) > 0);
            }
            $valueKey = $this->getValueKey((string) $key);
            $this->assertTrue($this->array->getRedis()->ttl($valueKey) > 0);

        }
        $this->assertTrue($this->array->getRedis()->ttl($this->getValueKey()) > 0);

    }

    protected function getValueKey($prefix = '')
    {
        return self::ARRAY_NAMESPACE . ($prefix !== '' ? ':' . $prefix : ':');
    }

	public function tearDown()
	{
		$this->array->delete();
		parent::tearDown();
	}

}
