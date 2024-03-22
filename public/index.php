<?php

# Just stop on any error, also warnings
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('exception_error_handler');

require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../app/defaults.php';

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

if (defined('DEBUG') && DEBUG) {$app->addErrorMiddleware(true, true, true);}

$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

$app->run();
