<?php

use Onboarding\GettingStartedSteps;
use Onboarding\Step;

class Unit_Onboarding_GettingStartedStepsTest extends \PHPUnit_Framework_TestCase
{


    /**
     * Create a GettingStartedSteps using an in-memory data provider and controlled data.
     *
     * @return GettingStartedSteps
     */
    private function createMockStepDataProvider()
    {
        $stepDataProvider = $this
            ->getMockBuilder('\Onboarding\InMemoryStepDataProvider')
            ->disableOriginalConstructor()
            ->getMock();
        return $stepDataProvider;
    }

    /**
     * Create a GettingStartedSteps using a mocked empty data provider.
     *
     * @return GettingStartedSteps
     */
    private function createEmptyService()
    {
        $stubStepDataProvider = $this->getMock('\Onboarding\StepDataProvider');

        $stubStepDataProvider
            ->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnValue(array()));

        $gettingStartedSteps = new GettingStartedSteps();
        $gettingStartedSteps->setStepDataProvider($stubStepDataProvider);
        return $gettingStartedSteps;
    }


    private function createStepFromStepData($stepData)
    {
        return new Step($stepData['id'], $stepData['config_id'],
            $stepData['group'], $stepData['text'], $stepData['url'],
            $stepData['completed_event']);
    }

    public function testFindStepByIdWithoutResult()
    {
        $stepDataProvider = $this->createMockStepDataProvider();
        $stepDataProvider->expects($this->once())->method('fetchOneByConfigId')->with('testNonExistentStep1')->will($this->returnValue(null));

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider);

        $step = $gettingStartedSteps->findStepByConfigId('testNonExistentStep1');
        $this->assertNull($step);
    }

    public function testFindStepById()
    {
        $stepId = 1;
        $returnStepData = array(
            'id' => $stepId,
            'config_id' => 'testStartingStep1',
            'url' => '/admin/index.php?ToDo=foo&wizard=1',
            'text' => '1. Test your GettingStartedSteps with this stub data provider.',
            'completed_event' => 'Wizard.Starting Step 1 Tested',
            'group' => Step::GROUP_STARTING,
        );

        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $configMock::staticExpects($this->any())
            ->method('get')
            ->with($this->equalTo('GettingStartedCompleted'))
            ->will($this->returnValue(array(
                'testStartingStep1',
        )));

        $stepDataProvider = new \Onboarding\InMemoryStepDataProvider(array(
            'testStartingStep1' => $returnStepData,
        ));

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);



        $step = $gettingStartedSteps->findStepById($stepId);

        $this->assertEquals(array_merge($returnStepData, array('is_complete' => true)), $step->toArray());
    }

    public function testFindStepByConfigId()
    {
        $configIdPrefix = 'testStartingStep';
        $configId = $configIdPrefix . '1';

        $returnStepData = $this->getStepData(1, Step::GROUP_STARTING, $configIdPrefix);

        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $configMock::staticExpects($this->any())
            ->method('get')
            ->with($this->equalTo('GettingStartedCompleted'))
            ->will($this->returnValue(array()));

        $stepDataProvider = new \Onboarding\InMemoryStepDataProvider(array(
            'testStartingStep1' => $returnStepData,
        ));

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);

        $step = $gettingStartedSteps->findStepByConfigId($configId);

        $expected = $returnStepData;
        $expected['is_complete'] = false;

        $this->assertEquals($expected, $step->toArray());
    }

    public function testIsNextWithNextStepId()
    {
        $configIdPrefix = 'testStartingStep';
        $configId = $configIdPrefix . '1';
        $returnStepData = $this->getStepData(1, Step::GROUP_STARTING, $configIdPrefix);

        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $configMock::staticExpects($this->any())
            ->method('get')
            ->with($this->equalTo('GettingStartedCompleted'))
            ->will($this->returnValue(array()));

        $stepDataProvider = new \Onboarding\InMemoryStepDataProvider(array(
            $configId => $returnStepData,
        ));

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);

        $this->assertTrue($gettingStartedSteps->isNextStep($this->createStepFromStepData($returnStepData)));
    }

    public function testIsNextWithNonNextStepId()
    {
        $configIdPrefix = 'testStartingStep';
        $configId = $configIdPrefix . '1';
        $returnStepData = $this->getStepData(1, Step::GROUP_STARTING, $configIdPrefix);

        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $configMock::staticExpects($this->any())
            ->method('get')
            ->with($this->equalTo('GettingStartedCompleted'))
            ->will($this->returnValue(array($configId)));

        $stepDataProvider = new \Onboarding\InMemoryStepDataProvider(array(
            $configId => $returnStepData,
        ));

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);

        $this->assertFalse($gettingStartedSteps->isNextStep($this->createStepFromStepData($returnStepData)));
    }

    public function testIsNextWithInvalidStep()
    {
        $configIdPrefix = 'testStartingStep';
        $configId = $configIdPrefix . '1';
        $returnStepData = $this->getStepData(1, Step::GROUP_STARTING, $configIdPrefix);

        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $configMock::staticExpects($this->any())
            ->method('get')
            ->with($this->equalTo('GettingStartedCompleted'))
            ->will($this->returnValue(array($configId)));

        $stepDataProvider = new \Onboarding\InMemoryStepDataProvider(array(
            $configId => $returnStepData,
        ));

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);

        $step = new Step(-1, '', '', '', '');

        $this->assertFalse($gettingStartedSteps->isNextStep($step));
    }

    public function testFindNextStepWithResult()
    {
        $id = 1;
        $configIdPrefix = 'testStartingStep';
        $configId = $configIdPrefix . '1';
        $returnStepData = $this->getStepData($id, Step::GROUP_STARTING, $configIdPrefix);

        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $configMock::staticExpects($this->any())
            ->method('get')
            ->with($this->equalTo('GettingStartedCompleted'))
            ->will($this->returnValue(array()));

        $stepDataProvider = new \Onboarding\InMemoryStepDataProvider(array(
            $configId => $returnStepData,
        ));

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);

        $step = $gettingStartedSteps->findNextStep();

        $this->assertInstanceOf('\Onboarding\Step', $step);
        $this->assertEquals($id, $step->getId());
    }

    public function testFindNextStepWithoutResult()
    {
        $id = 1;
        $configIdPrefix = 'testStartingStep';
        $configId = $configIdPrefix . '1';
        $returnStepData = $this->getStepData($id, Step::GROUP_STARTING, $configIdPrefix);

        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $configMock::staticExpects($this->any())
            ->method('get')
            ->with($this->equalTo('GettingStartedCompleted'))
            ->will($this->returnValue(array($configId)));

        $stepDataProvider = new \Onboarding\InMemoryStepDataProvider(array(
            $configId => $returnStepData,
        ));

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);

        $step = $gettingStartedSteps->findNextStep();
        $this->assertNull($step);
    }

    public function testFindAllStepsWithResults()
    {
        $id = 1;
        $configIdPrefix = 'testStartingStep';

        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $configMock::staticExpects($this->any())
            ->method('get')
            ->with($this->equalTo('GettingStartedCompleted'))
            ->will($this->returnValue(array($configIdPrefix . $id)));

        $stepDataProvider = $this->getSingleGroupStepDataProvider(1, Step::GROUP_STARTING, $id, $configIdPrefix);

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);

        $steps = $gettingStartedSteps->findAllSteps();
        $this->assertCount(1, $steps);
    }

    public function testFindAllStepsWithoutResults()
    {
        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $stepDataProvider = new \Onboarding\InMemoryStepDataProvider(array());

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);

        $steps = $gettingStartedSteps->findAllSteps();
        $this->assertEmpty($steps);
    }

    public function testFindStepsByValidGroup()
    {
        $stepCount = 2;
        $stepGroup = Step::GROUP_STARTING;
        $stepDataProvider = $this->getMixedGroupStepDataProvider(array(
            Step::GROUP_STARTING => $stepCount,
            Step::GROUP_MARKETING => 3,
        ));

        $configMock = $this->getMockClass('\Store_Config', array('get'));
        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);

        $steps = $gettingStartedSteps->findStepsByGroup($stepGroup);

        $this->assertEquals($stepCount, count($steps));
    }

    public function testFindStepsByInvalidGroup()
    {
        $stepDataProvider = $this->getSingleGroupStepDataProvider(1);

        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);

        $steps = $gettingStartedSteps->findStepsByGroup('Made Up Group');

        $this->assertEquals(0, count($steps));
    }

    public function testFindMatchingWithFilter()
    {
        $expectedStepCount = 2;
        $stepDataProvider = $this->getMixedGroupStepDataProvider(array(
            Step::GROUP_STARTING => $expectedStepCount,
            Step::GROUP_MARKETING => 3,
        ));

        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $configMock::staticExpects($this->any())
            ->method('get')
            ->with($this->equalTo('GettingStartedCompleted'))
            ->will($this->returnValue(array()));

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);


        $filter = new \DomainModel\Query\Filter(array(
            'group' => Step::GROUP_STARTING,
        ));

        $sorter = new \DomainModel\Query\Sorter('id', 'asc');


        $pager = $this->getMockBuilder('\DomainModel\Query\Pager')->disableOriginalConstructor()->getMock();
        $pager->expects($this->any())->method('page')->will($this->returnValue(1));
        $pager->expects($this->any())->method('limit')->will($this->returnValue(50));

        $steps = $gettingStartedSteps->findMatching($filter, $pager, $sorter);

        $this->assertEquals($expectedStepCount, count($steps));
        $this->assertEquals(1, $steps->current()->getId());

    }

    public function testFindMatchingOrderedDescendingById()
    {
        $stepsCount = 5;
        $stepDataProvider = $this->getSingleGroupStepDataProvider($stepsCount, Step::GROUP_STARTING, 1);

        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $configMock::staticExpects($this->any())
            ->method('get')
            ->with($this->equalTo('GettingStartedCompleted'))
            ->will($this->returnValue(array()));

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);


        $filter = new \DomainModel\Query\Filter(array());
        $pager = $this->getMockBuilder('\DomainModel\Query\Pager')->disableOriginalConstructor()->getMock();
        $pager->expects($this->any())->method('page')->will($this->returnValue(1));
        $pager->expects($this->any())->method('limit')->will($this->returnValue(50));
        $sorter = new \DomainModel\Query\Sorter('id', 'desc');

        $steps = $gettingStartedSteps->findMatching($filter, $pager, $sorter);

        $this->assertEquals($stepsCount, count($steps));
        $this->assertEquals($stepsCount, $steps->current()->getId());
    }

    public function testFindMatchingTwoPerPage()
    {
        $stepsCount = 5;
        $stepDataProvider = $this->getSingleGroupStepDataProvider($stepsCount, Step::GROUP_STARTING, 1);

        $configMock = $this->getMockClass('\Store_Config', array('get'));

        $configMock::staticExpects($this->any())
            ->method('get')
            ->with($this->equalTo('GettingStartedCompleted'))
            ->will($this->returnValue(array()));

        $gettingStartedSteps = new GettingStartedSteps($stepDataProvider, $configMock);


        $filter = new \DomainModel\Query\Filter(array());
        $pager = $this->getMockBuilder('\DomainModel\Query\Pager')->disableOriginalConstructor()->getMock();
        $pager->expects($this->any())->method('page')->will($this->returnValue(1));
        $pager->expects($this->any())->method('limit')->will($this->returnValue(2));
        $sorter = new \DomainModel\Query\Sorter('id', 'asc');


        $steps = $gettingStartedSteps->findMatching($filter, $pager, $sorter);

        $this->assertEquals(1, $steps->getCurrentPage());
        $this->assertEquals(3, $steps->getTotalPages());
    }

    private function getSingleGroupStepDataProvider($stepsCount, $stepGroup = Step::GROUP_STARTING, $firstId = 1, $configIdPrefix = 'testStep')
    {
        return $this->getMixedGroupStepDataProvider(array(
            $stepGroup => $stepsCount,
        ), $firstId, $configIdPrefix);
    }

    private function getMixedGroupStepDataProvider($groupPopulation = array(), $firstId = 1, $configIdPrefix = 'testStep')
    {
        $data = array();

        $groupFirstId = $firstId;

        foreach ($groupPopulation as $groupName => $groupFrequency) {
            $groupData = array();
            for ($i = $groupFirstId; $i < $groupFrequency + $groupFirstId; $i++) {
                $groupData["{$configIdPrefix}{$i}"] = $this->getStepData($i, $groupName, $configIdPrefix);
            }
            $groupFirstId += $groupFrequency;
            $data = array_merge($data, $groupData);
        }

        return new \Onboarding\InMemoryStepDataProvider($data);

    }

    private function getStepData($id, $stepGroup, $configIdPrefix)
    {
        return array(
            'id' => $id,
            'config_id' => "{$configIdPrefix}{$id}",
            'url' => "/admin/index.php?ToDo=testStep{$id}&wizard=1",
            'text' => "{$id}. Test your GettingStartedSteps with this stub data provider.",
            'completed_event' => "Wizard." . ucwords($stepGroup) . " Step {$id} Tested",
            'group' => $stepGroup,
        );
    }
}
