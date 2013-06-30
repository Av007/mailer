<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

$app->match('/config', function (Request $request) use ($app) {

    $form = $app['form.factory']->createBuilder('form', $app['swiftmailer.options'], array('csrf_protection' => false))
        ->add('host', 'text', array('required' => true))
        ->add('port', 'text', array('required' => true))
        ->add('username', 'text', array('required' => true))
        ->add('password', 'text', array('required' => true))
        ->add('encryption', 'text', array('required' => false))
        ->add('auth_mode', 'text', array('required' => false))
        ->getForm();

    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) {

            $data = $form->getData();

            // read config file
            $yaml = new Parser();
            $config = $yaml->parse(file_get_contents(__DIR__.'/../config.yml'));

            // apply configurations
            foreach ($data as $key=>$item) {

                if (array_key_exists($key, $config['app']['swiftmailer.options'])) {
                    $config['app']['swiftmailer.options'][$key] = $item;
                }
            }

            $dumper = new Dumper();
            $yaml = $dumper->dump($config);

            file_put_contents(__DIR__.'/../config.yml', $yaml);

            return $app->redirect($app['url_generator']->generate('home'));
        }
    } else {
        $config = new Config($app['swiftmailer.options']);
        $errors = $app['validator']->validate($config);

        if (count($errors) == 0) {
            return $app->redirect($app['url_generator']->generate('home'));
        }
    }

    return $app['twig']->render('config.html.twig', array(
        'form' => $form->createView(),
    ));
})->bind('config');
