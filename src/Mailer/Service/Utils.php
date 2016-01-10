<?php
/** Utils service */

namespace Mailer\Service;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Utils
 *
 * @package Mailer\Service
 * @author Vladimir Avdeev <avdeevvladimir@gmail.com>
 */
class Utils
{
    /**
     * @param string $file
     * @return array
     */
    public function readFile($file)
    {
        return Yaml::parse(file_get_contents($file));
    }

    /**
     * @param array $data
     * @param string $file
     */
    public function writeFile($data, $file)
    {
        file_put_contents($file, Yaml::dump($data));
    }

    /**
     * @param string $file
     */
    public function check($file)
    {
        if (!file_exists($file)) {
            touch($file);
            chmod($file, 0777);
        }
    }

    /**
     * @param string $file
     * @param array $data
     * @param string $key
     * @param string $cacheDir
     * @return bool
     */
    public function rewrite($file, $data, $key, $cacheDir = '')
    {
        $fileData = $this->readFile($file)['app'];

        if ($fileData && array_key_exists($key, $fileData)) {
            $fileData[$key] = $data[$key];

            $fs = new Filesystem();
            $fs->remove($file);

            if ($cacheDir) {
                $fileInfo = new \SplFileInfo($file);
                $fs->remove($this->getCacheName($fileInfo, $cacheDir));
            }

            $this->check($file);
            $this->writeFile(array('app' => $fileData), $file);

            return true;
        }

        return false;
    }

    /**
     * @param string $file
     * @param string $cacheDir
     * @param boolean $debug
     * @return array
     */
    public function cache($file, $cacheDir, $debug = false)
    {
        $fileInfo = new \SplFileInfo($file);
        $cacheFile = $this->getCacheName($fileInfo, $cacheDir);

        // the second argument indicates whether or not you want to use debug mode
        $userMatcherCache = new ConfigCache($cacheFile, $debug);
        if (!$userMatcherCache->isFresh()) {

            $data = Yaml::parse(file_get_contents($fileInfo));
            if (!$data) {
                throw new \LogicException('No data');
            }
            // the code for the UserMatcher is generated elsewhere
            $code = sprintf('<?php return %s;', var_export($data, true));

            $userMatcherCache->write($code);
        }

        // you may want to require the cached code:
        return require($cacheFile);
    }

    /**
     * @param \SplFileInfo $fileInfo
     * @param string $cacheDir
     * @return string
     */
    protected function getCacheName(\SplFileInfo $fileInfo, $cacheDir)
    {
        return $cacheDir . '/' . $fileInfo->getBasename($fileInfo->getExtension()) . 'php';
    }

    /**
     * @param array $parameters
     * @param array $errors
     * @param RecursiveValidator $validator
     * @return array|null|string
     */
    public function sendToParam($parameters, &$errors, RecursiveValidator $validator = null)
    {
        $errors = array();
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'send_to' => null
        ));

        $data = $resolver->resolve($parameters);
        /** @var array|string|null $sendTo */
        $sendTo = $data['send_to'];

        if (!is_array($sendTo)) {
            $sendTo = array_filter(explode(',', $data['send_to']));
            foreach ($sendTo as $item) {
                $errors[] = $validator->validateValue($item, new Assert\Email());
            }
        }

        return $sendTo ;
    }

    /**
     * @param \Swift_Mailer $mailer
     * @param string|array $sendTo
     * @param string $content
     */
    public function sendMail(\Swift_Mailer $mailer, $sendTo, $content)
    {
        $mailer->send(\Swift_Message::newInstance()
            ->setSubject('Test email')
            ->setFrom('test@mailer.com')
            ->setContentType('text/html')
            ->setTo($sendTo)
            ->setBody($content, 'text/html'));
    }

    /**
     * @param array $parameters
     * @param array $defaults
     * @return array
     */
    public function setDefaults($parameters, $defaults)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults($defaults);

        foreach ($parameters as $key => $parameter) {
            if (!$resolver->hasDefault($key)) {
                unset($parameters[$key]);
            };
        }

        return $resolver->resolve($parameters);
    }
}
