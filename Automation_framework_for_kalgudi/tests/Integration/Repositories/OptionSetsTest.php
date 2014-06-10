<?php
use Repository\OptionSets;
use DomainModel\Query\Filter;
use DomainModel\Query\Pager;
use DomainModel\Query\Sorter;

class OptionSetsTest extends PHPUnit_Framework_TestCase
{
    private $existCount = 0;
    private $newOptionSets= array();

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Interspire_DataFixtures::getInstance()->loadData('option_sets');
    }

    public function setUp()
    {
        $this->existCount = \Store_Product_Type::find()->count();
        for($i = 0 ; $i < 20 ; $i++) {
            $set = new \Store_Product_Type();
            $set->setName('test' . $i);
            $set->save();
            $this->newOptionSets[] = $set;
        }
    }

    public function tearDown()
    {
        foreach($this->newOptionSets as $set) {
            $set->delete();
        }
    }

    public function testFindMatchingNoParams()
    {
        $repository = new OptionSets();
        $result = $repository->findMatching(new Filter(array()), new Pager(), new Sorter());

        $this->assertEquals(($this->existCount + 20), $result->count());
        $this->assertArrayHasKey('assignedTo', $result->current());
        $this->assertArrayHasKey('ruleCount', $result->current());
    }

    public function testFindMatchingWithParams()
    {
        $repository = new OptionSets();
        $result = $repository->findMatching(
            new Filter(array('name' => 'test1')),
            new Pager(1, 10),
            new Sorter('name', 'asc'));

        $this->assertEquals(11, $result->count()); // test1 + test1X = 11 entries
        $this->assertArrayHasKey('assignedTo', $result->current());
        $this->assertArrayHasKey('ruleCount', $result->current());
    }

    public function testFindById()
    {
        $repository = new OptionSets();

        // colors - only some values
        $optionSet3001 = $repository->findById(3001);
        $this->assertJsonStringEqualsJsonFile(dirname(__FILE__) . '/data/optionSet3001.json', json_encode($optionSet3001));

        // colors - all values & sizes - only some values
        $optionSet3002 = $repository->findById(3002);
        $this->assertJsonStringEqualsJsonFile(dirname(__FILE__) . '/data/optionSet3002.json', json_encode($optionSet3002));
    }
}
