<?php
ini_set('display_errors', 1);

$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require __DIR__ . '/../app/app.php';

// load controllers
foreach (glob(__DIR__ . '/../app/controllers/*.php') as $filename) {
    include $filename;
}

// load entities
foreach (glob(__DIR__ . '/../app/entity/*.php') as $filename) {
    include $filename;
}

$app->run();
