<?php

namespace Unit\Job\Cron\Daily;

use Liip\Monitor\Result\CheckResult;
use Bigcommerce\SearchClient\Provider\ProviderInterface;

class TestElasticIndexHealth extends \PHPUnit_Framework_TestCase
{
    /** @var \Interspire_KeyStore|\PHPUnit_Framework_MockObject_MockObject */
    protected $keyStore;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    public function setUp()
    {
        $this->keyStore = $this
            ->getMockBuilder('Interspire_KeyStore')
            ->disableOriginalConstructor()
            ->setMethods(array('get', 'set'))
            ->getMock();

        $this->logger = $this
            ->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testPassingCheck()
    {
        $settings = new \Store_Settings(
            new \Store_Settings_Driver_Dummy(
                array(
                    'Feature_SearchIndexing' => true,
                )
            )
        );
        $settings->load();

        /** @var \Job_Cron_Daily_ElasticIndexHealth|\PHPUnit_Framework_MockObject_MockObject $job */
        $job = $this
            ->getMockBuilder('\Job_Cron_Daily_ElasticIndexHealth')
            ->disableOriginalConstructor()
            ->setMethods(array('check'))
            ->getMock();

        $elasticIndices = array(
            array(
                'type'  => ProviderInterface::TYPE_PRODUCT,
                'table' => 'products',
            ),
            array(
                'type'  => ProviderInterface::TYPE_CATEGORY,
                'table' => 'categories',
            ),
            array(
                'type'  => ProviderInterface::TYPE_BRAND,
                'table' => 'brands',
            ),
            array(
                'type'  => ProviderInterface::TYPE_PAGE,
                'table' => 'pages',
            ),
            array(
                'type'  => ProviderInterface::TYPE_POST,
                'table' => 'news',
            ),
        );

        foreach ($elasticIndices as $i => $index) {
            $job
                ->expects($this->at($i))
                ->method('check')
                ->with($this->equalTo($index['type']), $this->equalTo($index['table']))
                ->will($this->returnValue(new CheckResult('name', 'message', CheckResult::OK)));

            $this->keyStore
                ->expects($this->at(2 * $i))
                ->method('get')
                ->with($this->equalTo('elastic.index.' . $index['type'] . '.days_out_of_sync'))
                ->will($this->returnValue(0));

            $this->keyStore
                ->expects($this->at(2 * $i + 1))
                ->method('set')
                ->with($this->equalTo('elastic.index.' . $index['type'] . '.days_out_of_sync'), $this->equalTo(0));

            $this->logger
                ->expects($this->at($i))
                ->method('debug')
                ->with($this->equalTo('Elasticsearch index ' . $index['type'] . ' in sync'));
        }

        $job->setKeyStore($this->keyStore);
        $job->setLogger($this->logger);
        $job->setSettings($settings);

        $job->perform();
    }

    /**
     * Provides data for testFailingCheck, the number of days and the severity of the warning that should be generated.
     *
     * @return array
     */
    public function failingCheckSettings()
    {
        return array(
            array(0, 'warning'),
            array(2, 'error'),
            array(6, 'critical'),
        );
    }

    /**
     * @dataProvider failingCheckSettings()
     */
    public function testFailingCheck($days, $severity)
    {
        $settings = new \Store_Settings(
            new \Store_Settings_Driver_Dummy(
                array(
                    'Feature_SearchIndexing' => true,
                )
            )
        );
        $settings->load();

        /** @var \Job_Cron_Daily_ElasticIndexHealth|\PHPUnit_Framework_MockObject_MockObject $job */
        $job = $this
            ->getMockBuilder('\Job_Cron_Daily_ElasticIndexHealth')
            ->disableOriginalConstructor()
            ->setMethods(array('check'))
            ->getMock();

        $elasticIndices = array(
            array(
                'type'  => ProviderInterface::TYPE_PRODUCT,
                'table' => 'products',
            ),
            array(
                'type'  => ProviderInterface::TYPE_CATEGORY,
                'table' => 'categories',
            ),
            array(
                'type'  => ProviderInterface::TYPE_BRAND,
                'table' => 'brands',
            ),
            array(
                'type'  => ProviderInterface::TYPE_PAGE,
                'table' => 'pages',
            ),
            array(
                'type'  => ProviderInterface::TYPE_POST,
                'table' => 'news',
            ),
        );

        foreach ($elasticIndices as $i => $index) {
            $job
                ->expects($this->at($i))
                ->method('check')
                ->with($this->equalTo($index['type']), $this->equalTo($index['table']))
                ->will($this->returnValue(new CheckResult('name', 'message', CheckResult::WARNING)));

            $this->keyStore
                ->expects($this->at(2 * $i))
                ->method('get')
                ->with($this->equalTo('elastic.index.' . $index['type'] . '.days_out_of_sync'))
                ->will($this->returnValue($days));

            $this->keyStore
                ->expects($this->at(2 * $i + 1))
                ->method('set')
                ->with(
                    $this->equalTo('elastic.index.' . $index['type'] . '.days_out_of_sync'),
                    $this->equalTo($days + 1)
                );

            $this->logger
                ->expects($this->at($i))
                ->method($severity)
                ->with(
                    $this->equalTo('Elasticsearch index ' . $index['type'] . ' out of sync for '.($days + 1).' day(s)')
                );
        }

        $job->setKeyStore($this->keyStore);
        $job->setLogger($this->logger);
        $job->setSettings($settings);

        $job->perform();
    }
}
