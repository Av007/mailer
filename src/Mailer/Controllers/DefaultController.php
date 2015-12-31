<?php

namespace Mailer\Controllers;

use Mailer\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DefaultController
 *
 * @package Mailer\Controllers
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class DefaultController
{

    public function indexAction(Request $request)
    {
        $app = Application::getInstance()->getApp();

        // check lang
        if (!isset($app['lang'])) {
            return $app->redirect($app['url_generator']->generate('lang'));
        }

        // check config
        if (null === $app['session']->get('config')) {
            return $app->redirect($app['url_generator']->generate('config'));
        }

        // create form
        $result = false; // send flag
        $formMail = $app['form.factory']->createBuilder(Type\FormType::class)
            ->add('send_to', Type\TextType::class, array(
                'required' => true,
                'label' => 'Send to'
            ))
            ->add('content', Type\TextareaType::class, array(
                'required' => true,
                'label' => 'Content'
            ))
            ->getForm();

        // press send button
        if ('POST' == $request->getMethod()) {
            $formMail->bind($request);

            if ($formMail->isValid()) {
                $data = $formMail->getData();

                $send_to = explode(',', $data['send_to']);
                $send_to = array_filter($send_to);

                // add custom validation
                $emailConstraint = new Assert\Email();
                foreach ($send_to as &$item) {

                    $errors = $app['validator']->validateValue($item, $emailConstraint);
                    if (count($errors) > 0) {
                        return $app['twig']->render('index.html.twig', array(
                            'form' => $formMail->createView(),
                            'success' => $result,
                            'errors' => $errors
                        ));
                    }
                }

                $app['mailer']->send(\Swift_Message::newInstance()
                    ->setSubject('Test email')
                    ->setFrom('test@optimum-web.com')
                    ->setContentType("text/html")
                    ->setTo($send_to)
                    ->setBody($data['content'],'text/html'));

                $result = true;
            }
        }

        return $app['twig']->render('index.html.twig', array(
            'form'    => $formMail->createView(),
            'success' => $result,
        ));
    }
}
