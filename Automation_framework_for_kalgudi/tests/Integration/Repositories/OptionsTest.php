<?php
use Repository\Options;
use DomainModel\Query\Filter;
use DomainModel\Query\Pager;
use DomainModel\Query\Sorter;

class OptionsTest extends PHPUnit_Framework_TestCase
{
    private $existOptionCount = 0;
    private $newOptions= array();

    public function setUp()
    {
        $this->existOptionCount = \Store_Attribute::find()->count();
        for($i = 0 ; $i < 20 ; $i++) {
            $this->newOptions[] = $this->addDummyData('test' . $i);
        }
    }

    public function tearDown()
    {
        foreach($this->newOptions as $option) {
            $option->delete();
        }
    }

    private function addDummyData($name)
    {
        $attr = new Store_Attribute();
        $attr->setName($name);
        $attr->setDisplayName($name);
        $attr->setType(new Store_Attribute_Type_Configurable_PickList_Set);
        $attr->save();
        return $attr;
    }

    public function testFindMatchingNoParams()
    {
        $repository = new Options();
        $result = $repository->findMatching(new Filter(array()), new Pager(), new Sorter());

        $this->assertEquals(($this->existOptionCount + 20), $result->count());
        $this->assertArrayHasKey('type_name', $result->current());
    }

    public function testFindMatchingWithParams()
    {
        $repository = new Options();
        $result = $repository->findMatching(
            new Filter(array('name' => 'test1')),
            new Pager(1, 10),
            new Sorter('name', 'asc'));

        $this->assertEquals(11, $result->count()); // test1 + test1X = 11 entries
        $this->assertArrayHasKey('type_name', $result->current());
    }
}
