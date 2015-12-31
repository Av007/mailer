<?php

namespace Mailer\Controllers;

use Mailer\Application;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type;

/**
 * Class Language
 *
 * @package Mailer\Controllers
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class LanguageController
{
    public function indexAction(Request $request)
    {
        $app = Application::getInstance()->getApp();

        /** @var \Symfony\Component\Form\Form $form */
        $form = $app['form.factory']->createBuilder(Type\FormType::class, $app['swiftmailer.options'])
            ->add('lang', Type\ChoiceType::class, array(
                'required' => false,
                'choices' => array(
                    'en' => 'English',
                    'ru' => 'Русский'
                ),
                'placeholder' => 'default'
            ))
            ->getForm();

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                // read config file
                $yml = new Parser();
                $config = $yml->parse(file_get_contents(__DIR__ . '/../config.yml'));

                // apply configurations
                $data['lang'] = $data['lang'] ? $data['lang'] : 'en';
                $config['app']['lang'] = $data['lang'];

                // setup language
                $app['locale_fallback'] = $config['app']['lang'];

                // write config
                $dumper = new Dumper();
                file_put_contents(__DIR__ . '/../config.yml', $dumper->dump($config));

                return $app->redirect($app['url_generator']->generate('home'));
            }
        }

        return $app['twig']->render('lang.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
