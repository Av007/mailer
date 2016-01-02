<?php

namespace Mailer\Form;

use Symfony\Component\Form\Extension\Core\Type;

class LangType
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
        $this->formFactory = $formFactory;
        $this->data = $data;
        self::$instance = $this;
    }

    /**
     * Build form
     *
     * @return mixed
     */
    public function build()
    {
        return $this->formFactory->createBuilder(Type\FormType::class, $this->data)
            ->add('lang', Type\ChoiceType::class, array(
                'required' => false,
                'choices' => array(
                    'en' => 'English',
                    'ru' => 'Русский'
                ),
                'placeholder' => 'default'
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
