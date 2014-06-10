<?php

namespace Unit\Console\Profile\Import;

use Console\Commands\Profile\Import\DatasetCommand;

use Profile\Dataset\Shipping;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;

class DatasetCommandTest extends \PHPUnit_Framework_TestCase
{
    private $_command;
    private $_definition;

    public function setUp()
    {
        $this->_command    = new TestableDatasetCommand();
        $this->_definition = $this->_command->getDefinition();

        // Add the 'verbose' option as this is not added when we exeucte a command in this manner.
        $this->_definition->addOption(new InputOption('--verbose', null, InputOption::VALUE_NONE));

        $this->_datasetMock = $this->getMockClass('\\Profile\\Dataset', array('create'));
    }

    public function testConfiguration()
    {
        // Duplicate here so that it shows up in code coverage.
        $command    = new DatasetCommand();
        $definition = $command->getDefinition();
        $options    = $definition->getOptions();
        $arguments  = $definition->getArguments();

        $this->assertCount(2, $options);
        $this->assertCount(1, $arguments);
        $this->assertTrue($definition->hasOption('input-directory'));
        $this->assertTrue($definition->hasArgument('dataset-name'));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage No input directory specified.
     */
    public function testNoInputDirectory()
    {
        $input  = new ArrayInput(array('dataset-name' => 'shipping'), $this->_definition);
        $output = new NullOutput();

        $this->_command->execute($input, $output);
    }

    public function testImport()
    {
        $input = new ArrayInput(
            array(
                'dataset-name'      => 'shipping',
                '--input-directory' => 'test-directory',
            ),
            $this->_definition);

        $output = new NullOutput();

        $shippingDatasetMock = $this
            ->getMockBuilder('\\Profile\\Dataset\\Shipping')
            ->disableOriginalConstructor()
            ->setMethods(array('import'))
            ->getMock();

        $datasetMock = $this->_datasetMock;
        $datasetMock::staticExpects($this->at(0))
            ->method('create')
            ->with('shipping', 'test-directory')
            ->will($this->returnValue($shippingDatasetMock));

        $shippingDatasetMock
            ->expects($this->at(0))
            ->method('import')
            ->will($this->returnValue(0));

        $this->_command->execute($input, $output, $datasetMock);
    }
}

class TestableDatasetCommand extends DatasetCommand
{
    public function execute($input, $output, $datasetMock = null)
    {
        parent::execute($input, $output, $datasetMock);
    }
}

