<?php

if(!defined('DEBUG')) {define('DEBUG', false);}  # Should not be set to true in production
if (defined('DEBUG') && DEBUG) {error_reporting(E_ALL); ini_set('display_errors', '1');}

if(!defined('ZABBIXURL')) {define('ZABBIXURL', 'http://localhost/api_jsonrpc.php');}
if(!defined('USERSYAML')) {define('DASHBOARDSSYAML', '../dashboards.yaml');}
