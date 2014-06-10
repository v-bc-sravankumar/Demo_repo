<?php
namespace Unit\Lib\Console\Profile\Export;

use Console\Commands\Profile\Export\DatasetCommand;

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
        $this->_commandMock = $this
            ->getMockBuilder('\\Unit\\Lib\\Console\\Profile\\Export\\TestableDatasetCommand')
            ->setMethods(array('createDirectory'))
            ->getMock();

        $this->_definition  = $this->_commandMock->getDefinition();

        // Add the 'verbose' option as this is not added when we exeucte a command in this manner.
        $this->_definition->addOption(new InputOption('--verbose', null, InputOption::VALUE_NONE));

        $this->_datasetMock = $this->getMockClass('\\Profile\\Dataset', array('create'));

        $this->_shippingDatasetMock = $this
            ->getMockBuilder('\\Profile\\Dataset\\Shipping')
            ->disableOriginalConstructor()
            ->setMethods(array('export'))
            ->getMock();
    }

    public function testConfiguration()
    {
        // Duplicate here so that it shows up in code coverage.
        $command    = new DatasetCommand();
        $definition = $command->getDefinition();
        $options    = $definition->getOptions();
        $arguments  = $definition->getArguments();

        $this->assertCount(1, $options);
        $this->assertCount(1, $arguments);
        $this->assertTrue($definition->hasOption('output-directory'));
        $this->assertTrue($definition->hasArgument('dataset-name'));
    }

    public function testSuccess()
    {
        $input  = $this->_getArrayInput();
        $output = new NullOutput();

        $this->_prepareDatasetMock();
        $this->_prepareShippingDatasetMock();

        $this->_commandMock
            ->expects($this->at(0))
            ->method('createDirectory')
            ->with($this->equalTo('test-directory'), $this->isTrue());

        $this->_commandMock->execute($input, $output, $this->_datasetMock);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage No output directory specified.
     */
    public function testNoOutputDirectory()
    {
        $input  = $this->_getArrayInput(array('--output-directory' => null));
        $output = new NullOutput();

        $this->_commandMock->execute($input, $output);
    }

    /*** HELPERS ***/

    private function _getArrayInput($_parameters = array())
    {
        $parameters = array(
            '--output-directory' => 'test-directory',
            'dataset-name'       => 'shipping',
            '--verbose'          => false,
        );

        if (array_key_exists('--output-directory', $_parameters) && $_parameters['--output-directory'] === null) {
            unset($parameters['--output-directory']);
        }

        $input = new ArrayInput($parameters, $this->_definition);

        return $input;
    }

    private function _prepareDatasetMock()
    {
        $datasetMock = $this->_datasetMock;

        $datasetMock::staticExpects($this->at(0))
            ->method('create')
            ->with($this->equalTo('shipping'), $this->equalTo('test-directory'))
            ->will($this->returnValue($this->_shippingDatasetMock));
    }

    private function _prepareShippingDatasetMock()
    {
        $this->_shippingDatasetMock
            ->expects($this->at(0))
            ->method('export');
    }
}

class TestableDatasetCommand extends DatasetCommand
{
    public function execute($input, $output, $datasetMock = '\\Profile\\Dataset')
    {
        parent::execute($input, $output, $datasetMock);
    }
}

