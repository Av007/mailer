<?php

namespace Mailer\Form;

use Symfony\Component\Form\Extension\Core\Type;

/**
 * Class ConfigType
 *
 * @package Mailer\Form
 * @author Vladimir Avdeev
 */
class ConfigType
{
    /** @var \Symfony\Component\Form\Form $formFactory */
    protected $formFactory;
    /** @var array $data */
    protected $data;
    /** @var ConfigType $instance */
    protected static $instance;

    /**
     * @param \Symfony\Component\Form\Form $formFactory
     * @param array $data
     */
    public function __construct($formFactory, $data)
    {
        $this->data        = $data['email'];
        self::$instance    = $this;
        $this->formFactory = $formFactory;
    }

    /**
     * Build form
     *
     * @return mixed
     */
    public function build()
    {
        return $this->formFactory->createBuilder(Type\FormType::class, $this->data)
            ->add('host', Type\TextType::class, array(
                'label' => 'Host',
            ))
            ->add('port', Type\TextType::class, array(
                'label' => 'Port',
            ))
            ->add('username', Type\TextType::class, array(
                'label' => 'Username'
            ))
            ->add('password', Type\PasswordType::class, array(
                'label' => 'Password'
            ))
            ->add('encryption', Type\ChoiceType::class, array(
                'required' => false,
                'label'    => 'Encryption',
                'placeholder' => 'None',
                'choices'     => array(
                    'SSL'     => 'ssl',
                    'TSL'     => 'tsl',
                    'SSL/TLS' => 'ssl/tls',
                )
            ))
            ->add('auth_mode', Type\ChoiceType::class, array(
                'required'    => false,
                'label'       => 'Authentication mode',
                'placeholder' => 'None',
                'choices'     => array(
                    'Login'    => 'login',
                    'Sendmail' => 'sendmail',
                )
            ))
            ->add('save', Type\SubmitType::class, array(
                'attr' => array(
                    'class' => 'button'
                )
            ))
            ->getForm();
    }

    /**
     * Singleton
     *
     * @param \Symfony\Component\Form\Form $formFactory
     * @param array $data
     * @return ConfigType
     */
    public static function getInstance($formFactory, $data)
    {
        if (self::$instance === null) {
            self::$instance = new self($formFactory, $data);
        }

        return self::$instance;
    }
}
