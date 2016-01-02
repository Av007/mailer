<?php
/** Config service */

namespace Mailer\Service;

use Mailer\Application;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

/**
 * Class Config
 *
 * @package Mailer\Service
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class Config
{
    const FILE_NAME = '/config.yml';
    const TEST_NAME = 'testsuites.xml';

    /** @var Application $application */
    protected $application;
    /** @var string $configFile */
    protected $configFile;
    /** @var string $testFile */
    protected $testFile;

    public function __construct()
    {
        $this->application = Application::getInstance();
        $config = $this->application->getAppConfig();
        $this->configFile = $config['directories']['config'] . self::FILE_NAME;
        $this->testFile = $config['directories']['reports'] . self::TEST_NAME;
    }

    /**
     * @param array $data
     * @return array
     */
    public function populate($data)
    {
        $config = $this->read();
        $resolver = new OptionsResolver();

        foreach ($data as $key => $parameter) {
            if (!$resolver->hasDefault($key)) {
                unset($data[$key]);
            };
        }

        $resolver->setDefaults(array(
           'lang' => 'en'
        ));

        $data = $resolver->resolve($data);

        /**
         * @var string $key
         * @var string $item
         */
        foreach ($data as $key => $item) {
            if (array_key_exists($key, $config['app']['swiftmailer.options'])) {
                $config['app']['swiftmailer.options'][$key] = $item;
            }
        }

        // setup language
        $config['app']['lang'] = $data['lang'];
        $this->application['locale_fallback'] = $data['lang'];

        $this->write($config);

        return $config;
    }

    /**
     * @return mixed
     */
    public function read()
    {
        // read config file
        $yml = new Parser();
        /** @var array $config */
        return $yml->parse(file_get_contents($this->configFile));
    }

    /**
     * @param array $data
     */
    public function write($data)
    {
        $dumper = new Dumper();
        file_put_contents($this->configFile, $dumper->dump($data));
    }

    public function hasError()
    {
        // run and load phpunit test
        $output = shell_exec('cd '.MAIN_PATH . '&& phpunit --log-junit ' . $this->testFile . ' -c app/');

        // log tests

        $xml = null;
        if (file_exists($this->testFile) && file_get_contents($this->testFile)) {
            $xml = simplexml_load_file($this->testFile);
        }

        if(!$xml) {
            throw (new \Exception('Test is broken!') );
        }

        return ($xml->testsuite->attributes()->failures != 0) || ($xml->testsuite->attributes()->errors != 0);
    }
}
