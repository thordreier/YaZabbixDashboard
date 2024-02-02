<?php

# Just stop on any error, also warnings
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('exception_error_handler');

require_once __DIR__ . '/../settings.php';

if(!defined('DEBUG')) {define('DEBUG', false);}  # Should not be set to true in production
if (defined('DEBUG') && DEBUG) {error_reporting(E_ALL); ini_set('display_errors', '1');}

if(!defined('ZABBIXURL')) {define('ZABBIXURL', 'http://localhost/api_jsonrpc.php');}
if(!defined('USERSYAML')) {define('DASHBOARDSSYAML', '../dashboards.yaml');}

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

if (defined('DEBUG') && DEBUG) {$app->addErrorMiddleware(true, true, true);}

$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

$app->run();
