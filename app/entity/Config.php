<?php

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Config
{
    public $host;
    public $port;
    public $username;
    public $password;
    public $encryption;
    public $auth_mode;

    public function __construct($data)
    {
        $this->host = $data['host'];
        $this->port = $data['port'];
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->encryption = $data['encryption'];
        $this->auth_mode = $data['auth_mode'];
    }

    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('host', new Assert\NotBlank());
        $metadata->addPropertyConstraint('port', new Assert\NotBlank());
        $metadata->addPropertyConstraint('username', new Assert\Length(array('min' => 3)));
        $metadata->addPropertyConstraint('password', new Assert\Length(array('min' => 3)));
    }
}