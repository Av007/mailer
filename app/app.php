<?php

use Silex\Provider\FormServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Yaml\Parser;

$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new SwiftmailerServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new TranslationServiceProvider(), array(
    'translator.messages' => array(),
));
$app->register(new TwigServiceProvider(), array(
    'twig.path' => array(__DIR__.'/views'),
    //'twig.options' => array('cache' => __DIR__.'/../cache/twig'),
));

// read config file
$yaml = new Parser();
$config = $yaml->parse(file_get_contents(__DIR__.'/config.yml'));

// apply configurations
foreach ($config['app'] as $key=>$item) {
    if (isset($app[$key])) {
        $app[$key] = $item;
    }
}

return $app;