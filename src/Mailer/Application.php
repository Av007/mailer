<?php
/** Bootstrap file */

namespace Mailer;

use Mailer\Controller\DefaultController;
use Mailer\Service\Utils;
use Silex\Provider;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\Loader\YamlFileLoader;

/**
 * Class Bootstrap
 *
 * @package Mailer
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class Application extends Utils
{
    const FILE_NAME = '/config.yml';
    const TEMP_NAME = '/config.yml.dist';

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
                'cache'   => MAIN_PATH . 'app/cache',
                'config'  => MAIN_PATH . 'app/config',
                'reports' => MAIN_PATH . 'app/report/',
                'locales' => MAIN_PATH . 'app/locales/',
            ),
        ));
        $this->setApp(new \Silex\Application());
        $this->app->register(new Provider\SessionServiceProvider());

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
        $this->app->register(new Provider\ServiceControllerServiceProvider());
        $this->app->register(new Provider\TwigServiceProvider(), array(
            'twig.path'    => array($this->appConfig['directories']['view']),
            'twig.options' => array(
                'cache' => $this->appConfig['directories']['cache'] . '/twig'
            ),
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
            $translator->addResource('yaml', $this->appConfig['directories']['locales'] . 'ru.yml', 'ru', 'validators');

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

        $routies = $this->readConfig($this->appConfig['directories']['config'] . '/routes.yml');
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
        $session = $this->getSession();
        if ($session->get('configInit')) {
            $this->setConfig($session->get('configInit'));
            return;
        }
        // create config file
        $configFile = $this->appConfig['directories']['config'] . self::FILE_NAME;

        // create file
        $this->check($configFile);
        // read config file
        try {
            $config = $this->readConfig($configFile);
        } catch (\LogicException $e) {
            $config = $this->readFile($configFile);
        }

        // put defaults
        if (!$config) {
            $default = $this->readFile($this->appConfig['directories']['config'] . self::TEMP_NAME);
            $this->writeFile($default, $configFile);
            $config = $this->readConfig($configFile);
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

        $session->set('configInit', $data);
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
     * @param string $key
     * @param mixed $value
     * @param boolean $store
     */
    public function setConfigKey($key, $value, $store = false)
    {
        if (array_key_exists($key, $this->config)) {
            $this->config[$key] = $value;
        }

        if ($store) {
            $this->getSession()->set('configInit', $this->config);
        }
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
     * @param string $file
     * @return array
     */
    protected function readConfig($file)
    {
        return $this->cache(
            $file,
            $this->appConfig['directories']['cache'] . '/config',
            $this->getConfig('debug')
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSession()
    {
        return $this->app['session'];
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
