<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$app->match('/', function (Request $request) use ($app) {

//    $app['session']->set('checked', true)
//    $app['session']->get('checked')


    $config = new Config($app['swiftmailer.options']);
    $errors = $app['validator']->validate($config);

    if (count($errors) > 0) {
        return $app->redirect($app['url_generator']->generate('config'));
    }

    $result = false;
    $formMail = $app['form.factory']->createBuilder('form')
        ->add('send_to', 'text', array('required' => true))
        ->add('content', 'textarea', array('required' => true))
        ->getForm();

    if ('POST' == $request->getMethod()) {
        $formMail->bind($request);

        if ($formMail->isValid()) {
            $data = $formMail->getData();

            $send_to = explode(',', $data['send_to']);

            $app['mailer']->send(\Swift_Message::newInstance()
                ->setSubject('Test email')
                ->setFrom('test@optimum-web.com')
                ->setContentType("text/html")
                ->setTo(array_filter($send_to))
                ->setBody($data['content'],'text/html'));

            $result = true;
        }
    }

    return $app['twig']->render('index.html.twig', array(
        'form' => $formMail->createView(),
        'success' => $result
    ));
})->bind('home');
