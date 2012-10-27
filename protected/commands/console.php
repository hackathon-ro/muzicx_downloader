<?php

set_time_limit(0);
//ini_set('memory_limit', '256M');
ini_set('error_reporting', E_ALL);

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG', false);

// include Yii bootstrap file
$yii = '/usr/share/hackathon/yii/framework.1.1.8/yii.php';
require_once($yii);

// create application instance and run     
$config = dirname(__FILE__) . '/../config/console.php';

Yii::createConsoleApplication($config)->run();