<?php
/** Configuration model class */

namespace Mailer\Entity;

use Mailer\Validator\Constraints\ContainsConfig;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
    /** @var string $authMode */
    protected $authMode;
    /** @var string $lang */
    protected $lang;

    /**
     * @param array $parameters
     */
    public function __construct($parameters)
    {
        if ($parameters) {
            $resolver = new OptionsResolver();
            $resolver->setDefaults(array(
                'host'       => null,
                'port'       => null,
                'username'   => null,
                'password'   => null,
                'encryption' => null,
                'auth_mode'  => null,
            ));

            foreach ($parameters as $key => $parameter) {
                if (!$resolver->hasDefault($key)) {
                    unset($parameters[$key]);
                };
            }

            $data = $resolver->resolve($parameters);

            $this->host       = $data['host'];
            $this->port       = $data['port'];
            $this->username   = $data['username'];
            $this->password   = $data['password'];
            $this->encryption = $data['encryption'];
            $this->authMode   = $data['auth_mode'];

            $this->lang = 'en';
        }
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getEncryption()
    {
        return $this->encryption;
    }

    /**
     * @param string $encryption
     */
    public function setEncryption($encryption)
    {
        $this->encryption = $encryption;
    }

    /**
     * @return string
     */
    public function getAuthMode()
    {
        return $this->authMode;
    }

    /**
     * @param string $authMode
     */
    public function setAuthMode($authMode)
    {
        $this->authMode = $authMode;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
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
        $metadata->addConstraint(new ContainsConfig());
    }
}
