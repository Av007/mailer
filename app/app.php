<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Silex\Provider\FormServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Yaml\Parser;

$app = new Silex\Application();

$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new SwiftmailerServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new TranslationServiceProvider(), array(
    'translator.messages' => array(),
));
$app->register(new TwigServiceProvider(), array(
    'twig.path' => array(__DIR__.'/views'),
    //'twig.options' => array('cache' => __DIR__.'/../cache/twig'),
));

// create config file
if(!file_exists(__DIR__.'/config.yml')) {
    chmod(__DIR__.'/config.yml', 0777);
    file_put_contents(__DIR__.'/config.yml', file_get_contents(__DIR__.'/config.yml.dist'));
}

// read config file
$config = file_get_contents(__DIR__.'/config.yml');
$default = file_get_contents(__DIR__.'/config.yml.dest');

// put defaults
if (!$config) {
    file_put_contents(__DIR__.'/config.yml', $default);
    $config = $default;
}

// parse config as yaml
try {
    $yaml = new Parser();
    $config = $yaml->parse($config);
} catch (ParseException $e) {
    printf('Unable to parse the YAML string: %s', $e->getMessage());
    exit();
}

// check configurations
if (!isset($config['app'])) {
    printf('Configuration file doesn\'t exist!');
    exit();
}

// apply configurations
foreach ($config['app'] as $key=>$item) {
    if (isset($app[$key])) {
        $app[$key] = $item;
    }
}

return $app;
