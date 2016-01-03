<?php
/** Utils service */

namespace Mailer\Service;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Utils
 *
 * @package Mailer\Service
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class Utils
{
    /**
     * @param string $file
     * @return array
     */
    public function read($file)
    {
        return Yaml::parse(file_get_contents($file));
    }

    /**
     * @param array $data
     * @param string $file
     */
    public function write($data, $file)
    {
        file_put_contents($file, Yaml::dump($data));
    }

    /**
     * @param string $file
     */
    public function check($file)
    {
        if (!file_exists($file)) {
            touch($file);
            chmod($file, 0777);
        }
    }

    /**
     * @param array $parameters
     * @param array $errors
     * @param RecursiveValidator $validator
     * @return array|null|string
     */
    public function sendToParam($parameters, &$errors, RecursiveValidator $validator = null)
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
     * @param \Swift_Mailer $mailer
     * @param string|array $sendTo
     * @param string $content
     */
    public function sendMail(\Swift_Mailer $mailer, $sendTo, $content)
    {
        $mailer->send(\Swift_Message::newInstance()
            ->setSubject('Test email')
            ->setFrom('test@mailer.com')
            ->setContentType('text/html')
            ->setTo($sendTo)
            ->setBody($content, 'text/html'));
    }
}
