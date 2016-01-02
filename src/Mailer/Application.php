<?php
/** Bootstrap file */

namespace Mailer;

use Mailer\Controller\DefaultController;
use Silex\Provider;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Bootstrap
 *
 * @package Mailer
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class Application
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
            'debug'       => true,
            'directories' => array(
                'view'    => MAIN_PATH . 'app/view',
                'cache'   => MAIN_PATH . 'app/cache/twig',
                'config'  => MAIN_PATH . 'app/config',
                'reports' => MAIN_PATH . 'app/report/',
            ),
            'locale'      => array(
                'default'   => 'en',
                'directory' => MAIN_PATH . 'app/locales/',
            ),
        ));
        $this->setApp(new \Silex\Application());
        $this->app['debug'] = $this->appConfig['debug'];

        $this->initConfig();
        $this->initRoute();
        $this->registerServices();
    }

    /**
     * Register Silex services
     */
    protected function registerServices()
    {
        $this->app->register(new Provider\FormServiceProvider());
        $this->app->register(new Provider\ValidatorServiceProvider(), array(
            'validator.validator_service_ids' => array(
                'validator.config' => 'validator.config',
            )
        ));
        $this->app->register(new Provider\SwiftmailerServiceProvider(), $this->getConfig()['app']);
        $this->app->register(new Provider\UrlGeneratorServiceProvider());
        $this->app->register(new Provider\SessionServiceProvider());
        $this->app->register(new Provider\ServiceControllerServiceProvider());
        $this->app->register(new Provider\TwigServiceProvider(), array(
            'twig.path'    => array($this->appConfig['directories']['view']),
            'twig.options' => array($this->appConfig['directories']['cache']),
        ));
        $this->app->register(new Provider\TranslationServiceProvider(), array(
            'locale_fallback' => $this->appConfig['locale']['default'],
        ));

        // enable localization
        $this->app['translator'] = $this->app->share($this->app->extend('translator', function(\Silex\Translator $translator) {
            $translator->addLoader('yaml', new YamlFileLoader());

            $translator->addResource('yaml', $this->appConfig['locale']['directory'] . 'en.yml', 'en');
            $translator->addResource('yaml', $this->appConfig['locale']['directory'] . 'ru.yml', 'ru');

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
            $method = isset($route['method']) ? $route['method'] : 'GET';
            $this->app->match($route['path'], $route['defaults']['_controller'])->bind($name)->method($method);
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
        $default = file_get_contents($this->appConfig['directories']['config'] . '/config.yml.dist');
        $configFile = $this->appConfig['directories']['config'] . '/config.yml';

        if (!file_exists($configFile)) {
            touch($configFile);
            chmod($configFile, 0777);
            file_put_contents($configFile, $default);
            $config = $default;
        } else {
            // read config file
            $config = file_get_contents($configFile);
            // put defaults
            if (!$config) {
                file_put_contents($configFile, $default);
            }
        }

        // parse config as yaml
        $yaml = new Parser();
        $config = $yaml->parse($config);

        $this->setConfig($config);

        // check configurations
        if (!isset($config['app'])) {
            throw new \Exception('Configuration file doesn\'t exist!');
        }

        // apply configurations
        foreach ($config['app'] as $key => $item) {
            if (isset($this->app[$key])) {
                $this->app[$key] = $item;
            }
        }

        // apply localization
        $this->app->before(function () use ($config) {
            $config['app']['lang'] = isset($config['app']['lang']) ? $config['app']['lang'] : 'en';
            $this->app['lang']     = $config['app']['lang'];
            $this->app['locale']   = $config['app']['lang'];

            return $this->app;
        });
    }

    /**
     * @return array
     */
    public function getConfig()
    {
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
