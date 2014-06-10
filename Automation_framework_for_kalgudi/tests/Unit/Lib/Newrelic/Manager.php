<?php

namespace Unit\Lib\Newrelic;

use PHPUnit_Framework_TestCase;
use Newrelic\Manager;

class ManagerTest extends PHPUnit_Framework_TestCase
{
    public function getDetermineAppNameData()
    {
        $aggregateEnabled = 'Bigcommerce PHP Application';
        $aggregateDisabled = null;

        $data = array();

        $data[] = array('', $aggregateEnabled, 'Bigcommerce PHP Storefront;Bigcommerce PHP Application');
        $data[] = array('/', $aggregateEnabled, 'Bigcommerce PHP Storefront;Bigcommerce PHP Application');
        $data[] = array('/foo', $aggregateEnabled, 'Bigcommerce PHP Storefront;Bigcommerce PHP Application');
        $data[] = array('/admins', $aggregateEnabled, 'Bigcommerce PHP Storefront;Bigcommerce PHP Application');
        $data[] = array('/apis', $aggregateEnabled, 'Bigcommerce PHP Storefront;Bigcommerce PHP Application');
        $data[] = array('/davs', $aggregateEnabled, 'Bigcommerce PHP Storefront;Bigcommerce PHP Application');

        $data[] = array('/admin', $aggregateEnabled, 'Bigcommerce PHP Control Panel;Bigcommerce PHP Application');
        $data[] = array('/admin/', $aggregateEnabled, 'Bigcommerce PHP Control Panel;Bigcommerce PHP Application');
        $data[] = array('/admin/foo', $aggregateEnabled, 'Bigcommerce PHP Control Panel;Bigcommerce PHP Application');

        $data[] = array('/api', $aggregateEnabled, 'Bigcommerce PHP API;Bigcommerce PHP Application');
        $data[] = array('/api/', $aggregateEnabled, 'Bigcommerce PHP API;Bigcommerce PHP Application');
        $data[] = array('/api/foo', $aggregateEnabled, 'Bigcommerce PHP API;Bigcommerce PHP Application');

        $data[] = array('/dav', $aggregateEnabled, 'Bigcommerce PHP WebDAV;Bigcommerce PHP Application');
        $data[] = array('/dav/', $aggregateEnabled, 'Bigcommerce PHP WebDAV;Bigcommerce PHP Application');
        $data[] = array('/dav/foo', $aggregateEnabled, 'Bigcommerce PHP WebDAV;Bigcommerce PHP Application');

        $data[] = array('/admin', $aggregateDisabled, 'Bigcommerce PHP Control Panel');

        return $data;
    }

    /**
     * @dataProvider getDetermineAppNameData
     */
    public function testDetermineAppName($path, $aggregate, $expected)
    {
        $apps = array(
            '#^/admin($|/)#' => 'Bigcommerce PHP Control Panel',
            '#^/api($|/)#'   => 'Bigcommerce PHP API',
            '#^/dav($|/)#'   => 'Bigcommerce PHP WebDAV',
        );

        $manager = new Manager($apps, 'Bigcommerce PHP Storefront', $aggregate);
        $actual  = $manager->determineAppName($path);
        $this->assertSame($expected, $actual);
    }
}
