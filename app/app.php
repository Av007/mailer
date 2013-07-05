<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Silex\Provider\FormServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Translation\Loader\YamlFileLoader;

$app = new Silex\Application();

$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new SwiftmailerServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path' => array(__DIR__.'/views'),
    //'twig.options' => array('cache' => __DIR__.'/../cache/twig'),
));
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback' => 'en',
));

// enable localization
$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());

    $translator->addResource('yaml', __DIR__.'/locales/en.yml', 'en');
    $translator->addResource('yaml', __DIR__.'/locales/ru.yml', 'ru');

    return $translator;
}));

// create config file
$default = file_get_contents(__DIR__.'/config.yml.dest');

if(!file_exists(__DIR__.'/config.yml')) {
    chmod(__DIR__.'/config.yml', 0777);
    file_put_contents(__DIR__.'/config.yml', $default);
} else {
    // read config file
    $config = file_get_contents(__DIR__.'/config.yml');

    // put defaults
    if (!$config) {
        file_put_contents(__DIR__.'/config.yml', $default);
    }
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

// apply localization
$app->before(function () use ($app, $config) {
    $app['locale'] = $config['app']['lang'];
});

return $app;
