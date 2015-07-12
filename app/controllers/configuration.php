<?php

use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;

$app->match('/config', function (Request $request) use ($app) {
    /** @var Symfony\Component\Form\Form $form */
    $form = $app['form.factory']->createBuilder('form', $app['swiftmailer.options'])
        ->add('host', 'text', array(
            'required' => true,
            'label' => 'Host'
        ))
        ->add('port', 'text', array(
            'required' => true,
            'label' => 'Port'
        ))
        ->add('username', 'text', array(
            'required' => true,
            'label' => 'Username'
        ))
        ->add('password', 'password', array(
            'required' => true,
            'label' => 'Password'
        ))
        ->add('encryption', 'text', array(
            'required' => false,
            'label' => 'Encryption'
        ))
        ->add('auth_mode', 'text', array(
            'required' => false,
            'label' => 'Authentication mode'
        ))
        ->getForm();

    if ('POST' == $request->getMethod()) {
        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();

            // read config file
            $yml = new Parser();
            /** @var array $config */
            $config = $yml->parse(file_get_contents(__DIR__ . '/../config.yml'));

            /**
             * @var string $key
             * @var string $item
             */
            foreach ($data as $key => $item) {
                if (array_key_exists($key, $config['app']['swiftmailer.options'])) {
                    $config['app']['swiftmailer.options'][$key] = $item;
                }
            }

            // write config
            $dumper = new Dumper();
            file_put_contents(__DIR__ . '/../config.yml', $dumper->dump($config));
        }
    }

    // validate config structure
    $config = new Config($app['swiftmailer.options']);
    /** @var \Symfony\Component\Validator\ConstraintViolationList $errors */
    $errors = $app['validator']->validate($config);

    // run and load phpunit test
    shell_exec('phpunit --log-junit ../tests/Mailer/Reports/testsuites.xml -c ../phpunit.xml');
    $xml = simplexml_load_file('../tests/Mailer/Reports/testsuites.xml');

    if(!$xml) {
        throw (new \Exception('Test is broken!') );
    }

    // add custom validation
    if ($xml && ($xml->testsuite->attributes()->failures != 0)
        || ($xml->testsuite->attributes()->errors != 0)) {

        $validation = new ConstraintViolation($app['translator']->trans('error_config'), null, array(), null, null, null);
        $errors->add($validation);
    }

    // success
    if (count($errors) == 0) {
        $app['session']->set('config', true);
        return $app->redirect($app['url_generator']->generate('home'));
    }

    return $app['twig']->render('config.html.twig', array(
        'form' => $form->createView(),
        'errors' => $errors
    ));
})->bind('config');
