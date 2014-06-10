<?php

class Unit_Repositories_Brands extends PHPUnit_Framework_TestCase
{
    public function testFindAll()
    {
        $repository = new \Repository\Brands();
        $brands = $repository->findAll();

        // return the wanted proejctions
        $this->assertFalse(empty($brands));
        $this->assertArrayHasKey('id', $brands[0]);
        $this->assertArrayHasKey('name', $brands[0]);
        $this->assertArrayHasKey('image_file', $brands[0]);

        // make sure sorted by name asc
        $names = array();
        foreach($brands as $brand) {
            $names[] = strtolower($brand['name']);
        }
        $sorted = $names;
        sort($sorted);

        foreach($names as $index => $value) {
            $this->assertEquals($value, $sorted[$index]);
        }
    }
}
