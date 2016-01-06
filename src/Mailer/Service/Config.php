<?php
/** Config service */

namespace Mailer\Service;

use Mailer\Application;

/**
 * Class Config
 *
 * @package Mailer\Service
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class Config extends Utils
{
    const FILE_NAME = '/config.yml';
    const TEMP_NAME = '/config.yml.dist';
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
     * @param string|null $file
     * @return array
     */
    public function read($file = null)
    {
        if (!$file) {
            $file = $this->configFile;
        }

        return $this->readFile($file);
    }

    /**
     * @param string|null $file
     * @param array $data
     */
    public function write($data, $file = null)
    {
        if (!$file) {
            $file = $this->configFile;
        }

        $this->writeFile($data, $file);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function hasError()
    {
        // run and load phpunit test
        // log tests output
        shell_exec('cd ' . MAIN_PATH . '&& ' . MAIN_PATH . 'vendor/bin/phpunit --log-junit ' . $this->testFile . ' -c app/');

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
