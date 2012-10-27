<?php

$configFile = '/etc/hackathon/muzicx_downloader.conf';
$constants = parse_ini_file($configFile);
foreach ($constants as $constant => $value) {
    define(trim($constant), trim($value));
}

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'MUZICX downloader',
    // application components
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.helpers.*',
    ),
    'components' => array(
        'db' => array(
            'connectionString' => 'mysql:host=' . MYSQL_DB_HOST . ';port=' . MYSQL_DB_PORT . ';dbname=' . MYSQL_DB_DOWNLOADER,
            'emulatePrepare' => true,
            'username' => MYSQL_DB_USER,
            'password' => MYSQL_DB_PASS,
            'charset' => 'utf8',
            'schemaCachingDuration' => 0
        ),
    ),
);