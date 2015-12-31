<?php

namespace Mailer\Controllers;

use Mailer\Application;
use Mailer\Entity\Config;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class ConfigurationController
 *
 * @package Mailer\Controllers
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class ConfigurationController
{
    public function indexAction(Request $request)
    {
        $application = Application::getInstance();
        $app = $application->getApp();
        $appConfig = $application->getAppConfig();

        /** @var \Symfony\Component\Form\Form $form */
        $form = $app['form.factory']->createBuilder(Type\FormType::class, $app['swiftmailer.options'])
            ->add('host', Type\TextType::class, array(
                'label' => 'Host'
            ))
            ->add('port', Type\TextType::class, array(
                'label' => 'Port'
            ))
            ->add('username', Type\TextType::class, array(
                'label' => 'Username'
            ))
            ->add('password', Type\PasswordType::class, array(
                'label' => 'Password'
            ))
            ->add('encryption', Type\TextType::class, array(
                'required' => false,
                'label' => 'Encryption'
            ))
            ->add('auth_mode', Type\TextType::class, array(
                'required' => false,
                'label' => 'Authentication mode'
            ))
            ->getForm();

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $configFile = $appConfig['directories']['config'] . '/config.yml';

                // read config file
                $yml = new Parser();
                /** @var array $config */
                $config = $yml->parse(file_get_contents($configFile));

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
                file_put_contents($configFile, $dumper->dump($config));
            }
        }

        // validate config structure
        $config = new Config($app['swiftmailer.options']);
        /** @var \Symfony\Component\Validator\ConstraintViolationList $errors */
        $errors = $app['validator']->validate($config);

        // run and load phpunit test
        shell_exec('phpunit --log-junit ' . $appConfig['directories']['reports'] . 'testsuites.xml -c app/');
        $xml = simplexml_load_file($appConfig['directories']['reports'] . 'testsuites.xml');

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
            'form'   => $form->createView(),
            'errors' => $errors
        ));
    }
}
