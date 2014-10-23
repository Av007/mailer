<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

$app->match('/lang', function (Request $request) use ($app) {

    $form = $app['form.factory']->createBuilder('form', $app['swiftmailer.options'])
        ->add('lang', 'choice', array(
            'required' => false,
            'choices' => array('en' => 'English', 'ru' => 'Русский'),
            'empty_value' => 'default'
        ))
        ->getForm();

    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) {
            $data = $form->getData();

            // read config file
            $yaml = new Parser();
            $config = $yaml->parse(file_get_contents(__DIR__.'/../config.yml'));

            // apply configurations
            $data['lang'] = $data['lang'] ? $data['lang'] : 'en';
            $config['app']['lang'] = $data['lang'];

            // setup language
            $app['locale_fallback'] = $config['app']['lang'];

            // write config
            $dumper = new Dumper();
            file_put_contents(__DIR__.'/../config.yml', $dumper->dump($config));

            return $app->redirect($app['url_generator']->generate('home'));
        }
    }

    return $app['twig']->render('lang.html.twig', array(
        'form' => $form->createView(),
    ));
})->bind('lang');
