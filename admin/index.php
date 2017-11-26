<?php

$config_folder = dirname(__FILE__) . '/../application/config/';
$config_file = $config_folder . 'config.php';
if (!file_exists($config_file))
{
    $config_file = $config_folder . 'config-sample-mysql.php';
}
define('BASEPATH', dirname(__FILE__) . '/..'); // To prevent direct access not allowed
$config = require($config_file);

$urlStyle = $config['components']['urlManager']['urlFormat'];

// Simple redirect to still have the old /admin URL
if ($urlStyle == 'path') {
    header( 'Location: ../index.php/admin' );
} else {
    // For IIS use get style
    header( 'Location: ../index.php?r=admin' );
}