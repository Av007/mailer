<?php
/** Utils service */

namespace Mailer\Service;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Utils
 *
 * @package Mailer\Service
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class Utils
{
    /**
     * @param array $parameters
     * @param array $errors
     * @param \Symfony\Component\Validator\Validator\RecursiveValidator $validator
     * @return array|null|string
     */
    public function sendToParam($parameters, &$errors, $validator)
    {
        $errors = array();
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'send_to' => null
        ));

        $data = $resolver->resolve($parameters);
        /** @var array|string|null $sendTo */
        $sendTo = $data['send_to'];

        if (!is_array($sendTo)) {
            $sendTo = array_filter(explode(',', $data['send_to']));
            foreach ($sendTo as $item) {
                $errors[] = $validator->validateValue($item, new Assert\Email());
            }
        }

        return $sendTo ;
    }

    /**
     * @param $mailer
     * @param string|array $sendTo
     * @param string $content
     */
    public function sendMail($mailer, $sendTo, $content)
    {
        $mailer->send(\Swift_Message::newInstance()
            ->setSubject('Test email')
            ->setFrom('test@mailer.com')
            ->setContentType('text/html')
            ->setTo($sendTo)
            ->setBody($content, 'text/html'));
    }
}
