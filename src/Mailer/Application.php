<?php
/** Bootstrap file */

namespace Mailer;

use Mailer\Controllers\ConfigurationController;
use Mailer\Controllers\DefaultController;
use Mailer\Controllers\LanguageController;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Translation\Loader\YamlFileLoader;

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
                'view'    => __DIR__ . '/Views',
                'cache'   => __DIR__ . '/../../app/cache/twig',
                'config'  => __DIR__ . '/../../app/config',
                'reports' => __DIR__ . '/../../app/reports/',
            ),
            'locale'      => array(
                'default'   => 'en',
                'directory' => __DIR__ . '/../../app/locales/',
            ),
        ));
        $this->setApp(new \Silex\Application());
        $this->app['debug'] = $this->appConfig['debug'];

        $this->createConfig();
        $this->registerServices();

        /** @TODO: Adds routing */
        /*// load the routes
        $app -> register (new ConfigServiceProvider(__DIR__ . "/../config/routes.yml"));
        foreach ($app["config.routes"] as $name => $route) {
            $app -> match($route["pattern"], $route["defaults"]["_controller"]) -> bind($name) -> method(isset($route["method"]) ? $route["method"] : "GET");
        }*/

        $this->app['default.controller'] = $this->app->share(function() {
            return new DefaultController();
        });
        $this->app->get('/', "default.controller:indexAction");
        $this->app->match('/')->bind('home');

        $this->app['language.controller'] = $this->app->share(function() {
            return new LanguageController();
        });
        $this->app->get('/lang', "language.controller:indexAction");
        $this->app->match('/lang')->bind('lang');

        $this->app['configuration.controller'] = $this->app->share(function() {
            return new ConfigurationController();
        });
        $this->app->get('/config', "configuration.controller:indexAction");
        $this->app->match('/config')->bind('config');
    }

    /**
     * Register Silex services
     */
    protected function registerServices()
    {
        $this->app->register(new FormServiceProvider());
        $this->app->register(new ValidatorServiceProvider());
        $this->app->register(new SwiftmailerServiceProvider(), $this->getConfig()['app']);
        $this->app->register(new UrlGeneratorServiceProvider());
        $this->app->register(new SessionServiceProvider());
        $this->app->register(new ServiceControllerServiceProvider());
        $this->app->register(new TwigServiceProvider(), array(
            'twig.path'    => array($this->appConfig['directories']['view']),
            'twig.options' => array($this->appConfig['directories']['cache']),
        ));
        $this->app->register(new TranslationServiceProvider(), array(
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
     * Create config file
     *
     * @throws \Exception
     */
    protected function createConfig()
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
