<?php

use \org\bovigo\vfs\vfsStream;

/**
 * Define default configuration constants for tests if not overriden by phpunit.xml
 */

define('TEST_ENVIRONMENT', true);
if (!getenv('PHP_APP_ENVIRONMENT')) {
    putenv('PHP_APP_ENVIRONMENT=test');
}

if (!defined('TEST_ROOT')) {
    define('BUILD_ROOT', realpath(dirname(__FILE__).'/..'));
    define('SERVER_ROOT', realpath(dirname(BUILD_ROOT)));
    define('TEST_ROOT', BUILD_ROOT.'/tests');
}

if (!defined('TEST_DATA_ROOT')) {
    define('TEST_DATA_ROOT', dirname(__FILE__).'/Fixtures');
}

// Set up virtual file system for file lookup here
// because some of the integration tests have side effect
// of sending emails, which in turns need to parse a template file.
//
// When theme versioning is on by default, all theme file lookup happens
// outside the bc repository and therefore cannot be loaded in the old way,
// hence we need to setup the virtual file system to avoid template.php
// from throwing file cannot be opened exception.
//
// Theme file will be removed from the main bc app in the near future
// and any new tests that require any theme file please use vfs instead
// of relying on the assumption they will be there.
vfsStream::setup('test_theme_root');
$root = vfsStream::url('test_theme_root');

// setup for Redirect tests
mkdir("{$root}/Classic");
mkdir("{$root}/Classic/current");
file_put_contents("{$root}/Classic/current/default.html", "default");
file_put_contents("{$root}/Classic/current/404.html", "404");
file_put_contents("{$root}/Classic/current/config.json", "{}");
file_put_contents("{$root}/Classic/current/REVISION", "test-version");

// setup for any tests need to send emails
mkdir("{$root}/__emails");
mkdir("{$root}/__emails/current");
file_put_contents("{$root}/__emails/current/invoice_email.html", "email");
file_put_contents("{$root}/__emails/current/order_status_email.html", "email");

function getConstant($name, $default = '')
{
    if (defined($name)) {
        return constant($name);
    }

    $value = getenv($name) ?: $default;

    define($name, $value);
    return $value;
}

require_once BUILD_ROOT.'/config/init/autoloader.php';

Interspire_Autoloader::addPath(BUILD_ROOT.'/tests');
Interspire_Autoloader::register();

require_once BUILD_ROOT.'/config/app.php';
require_once BUILD_ROOT.'/config/init/environment.php';

$logger = new Monolog\Logger('Bigcommerce Integration', array(new Monolog\Handler\StreamHandler(STDERR)));

$dbServer       = getConstant('TEST_DB_SERVER', 'localhost');
$dbUser         = getConstant('TEST_DB_USER', 'root');
$dbPassword     = getConstant('TEST_DB_PASS');
$dbName         = getConstant('TEST_DB_NAME', str_replace('.', '', uniqid('test_', true)));
$applicationUrl = getConstant('TEST_APPLICATION_URL', 'http://localhost/' . basename(BUILD_ROOT));
$installSampleData  = !(bool)getenv('NO_SAMPLE_DATA');

if ($installSampleData) {
    $logger->info('Running tests with sample data...');
}
else {
    $logger->info('Running tests without sample data...');
}

$apploader = new Interspire_AppLoader($logger, $dbServer, $dbUser, $dbPassword, $dbName, $applicationUrl, $installSampleData);

// initialize and install the app
if (!$apploader->load()) {
    exit(1);
}

$logger->info('Running tests...');
