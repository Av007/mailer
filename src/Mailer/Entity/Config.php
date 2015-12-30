<?php
/** Configuration model class */

namespace Mailer\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class Config
 *
 * Used for manipulation with config file
 *
 * @package Mailer\Entity
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class Config
{
    /** @var string $host */
    protected $host;
    /** @var string $port */
    protected $port;
    /** @var string $username */
    protected $username;
    /** @var string $password */
    protected $password;
    /** @var string $encryption */
    protected $encryption;
    /** @var string $auth_mode */
    protected $auth_mode;
    /** @var string $lang */
    protected $lang;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        $this->host       = $data['host'];
        $this->port       = $data['port'];
        $this->username   = $data['username'];
        $this->password   = $data['password'];
        $this->encryption = isset($data['encryption']) ? $data['encryption'] : null;
        $this->auth_mode  = isset($data['auth_mode']) ? $data['auth_mode'] : null;
        $this->lang = 'en';
    }

    /**
     * @param ClassMetadata $metadata
     */
    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('host', new Assert\NotBlank());
        $metadata->addPropertyConstraint('port', new Assert\NotBlank());
        $metadata->addPropertyConstraint('username', new Assert\Length(array('min' => 3)));
        $metadata->addPropertyConstraint('password', new Assert\Length(array('min' => 3)));
    }
}
