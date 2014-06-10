<?php

namespace Unit\Job\Export;

use PHPUnit_Framework_TestCase;

class BatchExporterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No exporter specified.
     */
    public function testPerformEmptyClass()
    {
        /** @var \Job\Export\BatchExporter|\PHPUnit_Framework_MockObject_MockObject $exportJob */
        $exportJob = $this->getMockBuilder('Job\Export\BatchExporter')
            ->setMethods(array('updateJobProgress', 'errorLog'))
            ->setConstructorArgs(array(array()))
            ->getMock();

        $exportJob->expects($this->at(0))
            ->method('updateJobProgress')
            ->with(
                $this->equalTo(array('failed' => true)),
                $this->isNull(),
                $this->isFalse()
            );

        $exportJob->expects($this->at(1))
            ->method('errorLog')
            ->with($this->attributeEqualTo(
                'message',
                'No exporter specified.'
            ));

        $exportJob->perform();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid exporter class: invalid.
     */
    public function testPerformInvalidClass()
    {
        /** @var \Job\Export\BatchExporter|\PHPUnit_Framework_MockObject_MockObject $exportJob */
        $exportJob = $this->getMockBuilder('Job\Export\BatchExporter')
            ->setMethods(array('updateJobProgress', 'errorLog'))
            ->setConstructorArgs(array(array('exporter' => 'invalid')))
            ->getMock();

        $exportJob->expects($this->at(0))
            ->method('updateJobProgress')
            ->with(
                $this->equalTo(array('failed' => true)),
                $this->isNull(),
                $this->isFalse()
            );

        $exportJob->expects($this->at(1))
            ->method('errorLog')
            ->with($this->attributeEqualTo(
                'message',
                'Invalid exporter class: invalid.'
            ));

        $exportJob->perform();
    }

    public function testPerformWithoutOptions()
    {
        /** @var \Job\Export\BatchExporter|\PHPUnit_Framework_MockObject_MockObject $exportJob */
        $exportJob = $this->getMockBuilder('Job\Export\BatchExporter')
            ->setMethods(array('updateJobProgress', 'errorLog'))
            ->setConstructorArgs(array(array(
                'exporter' => 'ISC_ADMIN_EXPORTMETHOD_CSV',
                'session' => 'session-id',
            )))
            ->getMock();

        /** @var \ISC_ADMIN_EXPORTMETHOD_CSV|\PHPUnit_Framework_MockObject_MockObject $exportMethod */
        $exportMethod = $this->getMockBuilder('ISC_ADMIN_EXPORTMETHOD_CSV')
            ->disableOriginalConstructor()
            ->setMethods(array('Init', 'setExportSessionId', 'processExport'))
            ->getMock();

        // Since we've not passed 'options' to the constructor of $exportJob,
        // we do not expect Init() to be called.
        $exportMethod->expects($this->never())
            ->method('Init');

        $exportMethod->expects($this->at(0))
            ->method('setExportSessionId')
            ->with($this->equalTo('session-id'));

        $exportMethod->expects($this->at(1))
            ->method('processExport');

        $exportJob->setExporter($exportMethod);
        $exportJob->perform();
    }

    public function testPerform()
    {
        /** @var \Job\Export\BatchExporter|\PHPUnit_Framework_MockObject_MockObject $exportJob */
        $exportJob = $this->getMockBuilder('Job\Export\BatchExporter')
            ->setMethods(array('updateJobProgress', 'errorLog'))
            ->setConstructorArgs(array(array(
                'exporter' => 'ISC_ADMIN_EXPORTMETHOD_CSV',
                'session'  => 'session-id',

                'options' => array(
                    'filetype'    => array('name' => 'products'),
                    'template_id' => 'template-id',
                    'where'       => 'where',
                    'having'      => 'having',
                ),
            )))
            ->getMock();

        /** @var \ISC_ADMIN_EXPORTMETHOD_CSV|\PHPUnit_Framework_MockObject_MockObject $exportMethod */
        $exportMethod = $this->getMockBuilder('ISC_ADMIN_EXPORTMETHOD_CSV')
            ->disableOriginalConstructor()
            ->setMethods(array('Init', 'setExportSessionId', 'processExport'))
            ->getMock();

        /** @var \ISC_ADMIN_EXPORTFILETYPE|\PHPUnit_Framework_MockObject_MockObject $fileType */
        $fileType = $this->getMockBuilder('ISC_ADMIN_EXPORTFILETYPE_PRODUCTS')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \ISC_ADMIN_EXPORTOPTIONS|\PHPUnit_Framework_MockObject_MockObject $options */
        $options = new \ISC_ADMIN_EXPORTOPTIONS();
        $options->setFileType($fileType)
            ->setTemplateId('template-id')
            ->setWhere('where')
            ->setHaving('having');

        $exportMethod->expects($this->at(0))
            ->method('Init')
            ->with($this->equalTo($options));

        $exportMethod->expects($this->at(1))
            ->method('setExportSessionId')
            ->with($this->equalTo('session-id'));

        $exportMethod->expects($this->at(2))
            ->method('processExport');

        $exportJob->setExporter($exportMethod);
        $exportJob->setFileType($fileType);
        $exportJob->perform();
    }
}
