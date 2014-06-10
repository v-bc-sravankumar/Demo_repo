<?php

namespace Integration\Console;

use PHPUnit_Framework_TestCase;
use Console\Application;
use Config\Environment;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ApplicationTest extends PHPUnit_Framework_TestCase
{
    protected $currentEnv;

    public function setUp()
    {
        $this->currentEnv = Environment::name();
        putenv('PHP_APP_ENVIRONMENT=');
    }

    public function tearDown()
    {
        Environment::define($this->currentEnv);
        putenv('PHP_APP_ENVIRONMENT=' . $this->currentEnv);
    }

    public function testDefaultEnvironmentIsDevelopment()
    {
        $app = new Application();
        $app->doRun(new ArrayInput(array()), new NullOutput());

        $this->assertEquals('development', Environment::name());
    }

    public function testEnvironmentOptionSetsEnvironent()
    {
        $app = new Application();
        $app->doRun(new ArrayInput(array('--env' => 'development')), new NullOutput());

        $this->assertEquals('development', Environment::name());
    }

    public function testPhpAppEnvironmentOverridesDefaultEnvironment()
    {
        putenv('PHP_APP_ENVIRONMENT=production');

        $app = new Application();
        $app->doRun(new ArrayInput(array()), new NullOutput());

        $this->assertEquals('production', Environment::name());
    }
}
