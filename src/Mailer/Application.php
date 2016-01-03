<?php
/** Bootstrap file */

namespace Mailer;

use Mailer\Controller\DefaultController;
use Mailer\Service\Config;
use Mailer\Service\Utils;
use Silex\Provider;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Bootstrap
 *
 * @package Mailer
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class Application extends Utils
{
    /** @var \Silex\Application $app */
    protected $app;
    /** @var array $appConfig */
    protected $appConfig = array();
    /** @var array $config */
    protected $config = array();
    /** @var Application $instance */
    protected static $instance;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * Initialization
     *
     * @throws \Exception
     */
    public function init()
    {
        $this->setAppConfig(array(
            'directories' => array(
                'view'    => MAIN_PATH . 'app/view',
                'cache'   => MAIN_PATH . 'app/cache/twig',
                'config'  => MAIN_PATH . 'app/config',
                'reports' => MAIN_PATH . 'app/report/',
                'locales' => MAIN_PATH . 'app/locales/',
            ),
        ));
        $this->setApp(new \Silex\Application());

        $this->initConfig();
        $this->initRoute();
        $this->registerServices();
    }

    /**
     * Register Silex services
     */
    protected function registerServices()
    {
        $this->app['debug'] = $this->getConfig('debug');
        $this->app->register(new Provider\TranslationServiceProvider(), array(
            'locale_fallback' => $this->getConfig('default_language'),
        ));
        $this->app->register(new Provider\FormServiceProvider());
        $this->app->register(new Provider\ValidatorServiceProvider(), array(
            'validator.validator_service_ids' => array(
                'validator.config' => 'validator.config',
            )
        ));
        $this->app->register(new Provider\SwiftmailerServiceProvider(), array(
            'swiftmailer.options' => $this->getConfig('email')
        ));
        $this->app->register(new Provider\UrlGeneratorServiceProvider());
        $this->app->register(new Provider\SessionServiceProvider());
        $this->app->register(new Provider\ServiceControllerServiceProvider());
        $this->app->register(new Provider\TwigServiceProvider(), array(
            'twig.path'    => array($this->appConfig['directories']['view']),
            'twig.options' => array($this->appConfig['directories']['cache']),
        ));

        // apply localization
        $this->app->before(function () {
            $this->app['lang']   = $this->getConfig('default_language');
            $this->app['locale'] = $this->getConfig('default_language');

            return $this->app;
        });
        // enable localization
        $this->app['translator'] = $this->app->share($this->app->extend('translator', function(\Silex\Translator $translator) {
            $translator->addLoader('yaml', new YamlFileLoader());
            $translator->addResource('yaml', $this->appConfig['directories']['locales'] . 'en.yml', 'en');
            $translator->addResource('yaml', $this->appConfig['directories']['locales'] . 'ru.yml', 'ru');

            return $translator;
        }));
    }

    /**
     * Init route
     */
    protected function initRoute()
    {
        $this->app['default.controller'] = $this->app->share(function() {
            return new DefaultController();
        });

        $routies = Yaml::parse(file_get_contents($this->appConfig['directories']['config'] . '/routes.yml'));
        foreach ($routies as $name => $route) {
            if (isset($route['method'])) {
                $this->app->match($route['path'], $route['defaults']['_controller'])
                          ->bind($name)
                          ->method($route['method']);
            } else {
                $this->app->match($route['path'], $route['defaults']['_controller'])
                          ->bind($name);
            }
        }
    }

    /**
     * Create config file
     *
     * @throws \Exception
     */
    protected function initConfig()
    {
        // create config file
        $default = $this->read($this->appConfig['directories']['config'] . Config::TEMP_NAME);
        $configFile = $this->appConfig['directories']['config'] . Config::FILE_NAME;

        // create file
        $this->check($configFile);
        // read config file
        $config = $this->read($configFile);
        // put defaults
        if (!$config) {
            $this->write($default, $configFile);
            $config = $default;
        }

        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'debug'            => false,
            'default_language' => 'en',
            'languages'        => array('en', 'ru'),
            'email'            => array(),
        ));

        // check configurations
        if (!isset($config['app'])) {
            throw new \Exception('Configuration file doesn\'t exist!');
        }
        $data = $resolver->resolve($config['app']);
        $this->setConfig($data);
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function getConfig($key = null)
    {
        if ($key && array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return \Silex\Application
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param \Silex\Application $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }

    /**
     * @return array
     */
    public function getAppConfig()
    {
        return $this->appConfig;
    }

    /**
     * @param array $appConfig
     */
    public function setAppConfig($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    /**
     * Singleton
     *
     * @return Application
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
