<?php

namespace Mailer\Tests;

use Silex\WebTestCase;

class Test extends WebTestCase
{
    protected $app;

    public function createApplication()
    {
        $app = require __DIR__.'/../../../app/app.php';

        $app['mailer.logger'] = new \Swift_Plugins_MessageLogger();
        $app['mailer']->registerPlugin($app['mailer.logger']);

        $this->app = $app;

        return $app;
    }

    /**
     * test socket connection
     */
    public function testSocketTest()
    {
        // socket test
        $socket = (bool)fsockopen($this->app['swiftmailer.options']['host'], $this->app['swiftmailer.options']['port']);
        $this->assertEquals($socket, true, "Socket true");
    }

    /**
     * test email authentication
     */
    public function testAuthTest()
    {
        $transport = \Swift_SmtpTransport::newInstance($this->app['swiftmailer.options']['host'],
            $this->app['swiftmailer.options']['port'])
            ->setUsername($this->app['swiftmailer.options']['username'])
            ->setPassword($this->app['swiftmailer.options']['password']);

        try {
            $transport->start();
        } catch (\Exception $e) {
            $this->assertFalse(TRUE);
        }

        $this->assertTrue(TRUE);
    }

    /**
     * test send mail
     */
    public function testSendTest()
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Test email')
            ->setFrom('test@test.com')
            ->setContentType("text/html")
            ->setTo('v.avdeev@optimum-web.com')
            ->setBody('Test!','text/html');

        $result = $this->app['mailer']->send($message);
        $this->assertEquals($result, 1, "Sent to spool");

        $this->assertEquals(1, $this->app['mailer.logger']->countMessages(), "Only one email sent");

        $emails = $this->app['mailer.logger']->getMessages();
        $this->assertEquals("Test email", $emails[0]->getSubject(), "Subject is correct");
    }
}