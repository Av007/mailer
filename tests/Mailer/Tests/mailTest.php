<?php

namespace Mailer\Tests;

use Silex\WebTestCase;

class Test extends WebTestCase
{
    protected $app;

    public function createApplication()
    {
        $app = require __DIR__.'/../../../app/app.php';

        $app["swiftmailer.transport"] = new \Swift_Transport_NullTransport($app['swiftmailer.transport.eventdispatcher']);
        $this->app = $app;

        return $app;
    }

    public function testSendTest()
    {
        if (fsockopen($this->app['swiftmailer.options']['host'], $this->app['swiftmailer.options']['port'])) {
            die("sad");
        }

        $message = \Swift_Message::newInstance()
            ->setSubject('Test email')
            ->setFrom('test@test.com')
            ->setContentType("text/html")
            ->setTo('v.avdeev@optimum-web.com')
            ->setBody('Test!','text/html');

        $result = $this->app['mailer']->send($message);

        $this->assertEquals($result, 1, "Subject is correct");
    }
}