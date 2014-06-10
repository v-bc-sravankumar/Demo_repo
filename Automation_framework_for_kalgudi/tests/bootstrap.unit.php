<?php

// set to the same timezone as the app is run in
date_default_timezone_set('GMT');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/../config/init/autoloader.php';
require_once __DIR__.'/../config/init/functions.php';
$app = require_once ISC_BASE_PATH . '/config/app.php';
require_once __DIR__.'/../config/init/paths.php';

define('TEST_ENVIRONMENT', true);

if (!getenv('PHP_APP_ENVIRONMENT')) {
    putenv('PHP_APP_ENVIRONMENT=test');
}

require_once __DIR__.'/../config/init/environment.php';
