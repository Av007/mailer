<?php

namespace Mailer\Controller;

use Mailer\Application;
use Mailer\Entity;
use Mailer\Form;
use Mailer\Service;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DefaultController
 *
 * @package Mailer\Controllers
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class DefaultController
{
    /**
     * Homepage
     */
    public function indexAction(Request $request)
    {
        $app = Application::getInstance()->getApp();
        $errors = array();

        // check config
        if (!$app['session']->get('config')) {
            return $app->redirect($app['url_generator']->generate('config'));
        }

        /** @var \Symfony\Component\Form\Form $form */
        $form = Form\SendType::getInstance($app['form.factory'])->build();

        // press send button
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                $utils = new Service\Utils();
                $sendTo = $utils->sendToParam($data, $errors, $app['validator']);

                if (count($errors) == 0) {
                    $utils->sendMail($app['mailer'], $sendTo, $data['content']);
                    $app['session']->getFlashBag()->add('success', 'message_sent');

                    return $app->redirect($app['url_generator']->generate('home'));
                }
            }
        }

        return $app['twig']->render('index.html.twig', array(
            'form'   => $form->createView(),
            'errors' => $errors,
        ));
    }

    /**
     * Language
     */
    public function langAction(Request $request)
    {
        $application = Application::getInstance();
        $app = $application->getApp();

        /** @var \Symfony\Component\Form\Form $form */
        $form = Form\LangType::getInstance($app['form.factory'], array('lang' => $app['session.default_locale']))->build();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $application->setConfigKey('default_language', $form->get('lang')->getData(), true);

                return $app->redirect($app['url_generator']->generate('home'));
            }
        }

        return $app['twig']->render('lang.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Configuration
     */
    public function configAction(Request $request)
    {
        $application = Application::getInstance();
        $app = $application->getApp();
        $config = $application->getConfig('email');

        $form = Form\ConfigType::getInstance($app['form.factory'], $config)->build();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $config = $form->getData();
            }
        }

        // validate config structure
        /** @var \Symfony\Component\Validator\ConstraintViolationList $errors */
        $errors = $app['validator']->validate(new Entity\Config($config));

        // success
        $app['session']->set('config', count($errors) == 0);
        if (count($errors) == 0) {
            $application->setConfigKey('email', $config, true);
            return $app->redirect($app['url_generator']->generate('home'));
        }

        return $app['twig']->render('config.html.twig', array(
            'form'   => $form->createView(),
            'errors' => $errors
        ));
    }
}
