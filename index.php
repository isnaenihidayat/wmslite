<?php
//die('sedang maintenance');
define('YII_ENABLE_ERROR_HANDLER', true);
define('YII_ENABLE_EXCEPTION_HANDLER', true);
ini_set("display_errors",true);

date_default_timezone_set('Asia/Jakarta');

$loader = require __DIR__ . '/vendor/autoload.php';

// Enable debug mode for development environment
//defined( 'YII_DEBUG' ) or define( 'YII_DEBUG', true );
  
// Specify how many levels of call stack should be shown in each log message
defined( 'YII_TRACE_LEVEL' ) or define( 'YII_TRACE_LEVEL', 3 );

// include Yii bootstrap file
require_once(dirname(__FILE__).'/yii-1.1.28/yii.php');
$config=dirname(__FILE__).'/protected/config/main.php';

// create a Web application instance and run
Yii::createWebApplication($config)->run();
