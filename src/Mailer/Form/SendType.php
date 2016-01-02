<?php

namespace Mailer\Form;

use Symfony\Component\Form\Extension\Core\Type;

class SendType
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
        return $this->formFactory->createBuilder(Type\FormType::class)
            ->add('send_to', Type\TextType::class, array(
                'required' => true,
                'label' => 'Send to'
            ))
            ->add('content', Type\TextareaType::class, array(
                'required' => true,
                'label' => 'Content'
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
    public static function getInstance($formFactory, $data = array())
    {
        if (self::$instance === null) {
            self::$instance = new self($formFactory, $data);
        }

        return self::$instance;
    }
}
