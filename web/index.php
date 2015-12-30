<?php
/** Index file */
require_once __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Mailer\Application();
$bootstrap->init();

if (in_array(php_sapi_name(), array(
    'cli',
    'cli-server',
), true)) {

    header('Content-Type: cli');

    if (isset($_SERVER['REQUEST_URI'])) {
        $filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
    } else {
        $filename = '';
    }
    return;


    if (is_file($filename)) {
        return;
    }
}


$bootstrap->getApp()->run();
