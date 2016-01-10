<?php
/** Configuration model class */

namespace Mailer\Entity;

use Mailer\Application;
use Mailer\Service\Utils;
use Mailer\Validator\Constraints\ContainsConfig;
use Mailer\Validator\Constraints\ContainsConfigValidator as ConfigValidator;
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
class Config extends Utils
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
            $data = $this->setDefaults($parameters, array(
                'email'            => null,
                'default_language' => 'en',
            ));

            if ($data['email']) {
                $emailConfig = $this->setDefaults($data['email'], array(
                    'host'       => null,
                    'port'       => null,
                    'username'   => null,
                    'password'   => null,
                    'encryption' => null,
                    'auth_mode'  => null,
                ));

                $this->host       = $emailConfig['host'];
                $this->port       = $emailConfig['port'];
                $this->username   = $emailConfig['username'];
                $this->password   = $emailConfig['password'];
                $this->encryption = $emailConfig['encryption'];
                $this->authMode   = $emailConfig['auth_mode'];
            }

            $this->lang = $data['default_language'];
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
     * @return bool
     * @throws \Exception
     */
    public function checkReport()
    {
        $config = Application::getInstance()->getAppConfig();
        $testFile = $config['directories']['reports'] . ConfigValidator::TEST_NAME;

        $xml = null;
        if (file_exists($testFile) && file_get_contents($testFile)) {
            $xml = simplexml_load_file($testFile);
        }

        if(!$xml) {
            throw (new \Exception('Test is broken!') );
        }

        return ($xml->testsuite->attributes()->failures != 0) || ($xml->testsuite->attributes()->errors != 0);
    }

    public function getFileName()
    {
        $config = Application::getInstance()->getAppConfig();
        return $config['directories']['config'] . Application::FILE_NAME;
    }

    public function save()
    {
        $application = Application::getInstance();
        $config = $application->getAppConfig();

        $this->rewrite($this->getFileName(), $this->toArray(), 'email', $config['directories']['cache'] . '/config');
        $application->setConfigKey('email', $this->toArray()['email'], true);
    }

    /**
     * @param ClassMetadata $metadata
     */
    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('host', new Assert\NotBlank());
        $metadata->addPropertyConstraint('port', new Assert\NotBlank());
        $metadata->addPropertyConstraint('port', new Assert\Range(array(
            'min' => 0,
            'max' => 9999,
        )));
        $metadata->addPropertyConstraint('username', new Assert\Length(array('min' => 3)));
        $metadata->addPropertyConstraint('password', new Assert\Length(array('min' => 3)));
        $metadata->addConstraint(new ContainsConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array(
            'email' => array(
                'host'       => $this->getHost(),
                'port'       => $this->getPort(),
                'username'   => $this->getUsername(),
                'password'   => $this->getPassword(),
                'encryption' => $this->getEncryption(),
                'auth_mode'  => $this->getAuthMode(),
            )
        );
    }
}
