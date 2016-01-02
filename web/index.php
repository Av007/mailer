<?php
/** Index file */
defined('MAIN_PATH') || define('MAIN_PATH', realpath(__DIR__) . '/../');

require_once MAIN_PATH . 'vendor/autoload.php';

$bootstrap = new Mailer\Application();
$bootstrap->init();

if (in_array(php_sapi_name(), array(
    'cli',
    'cli-server',
), true)) {
    header('Content-Type: cli');
    return;
}

// runs application
$bootstrap->getApp()->run();
