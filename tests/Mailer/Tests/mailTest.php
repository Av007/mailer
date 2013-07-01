<?php

namespace Mailer\Tests;

use Silex\WebTestCase;

class Test extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../../../app/app.php';

        $app["swiftmailer.transport"] = new \Swift_Transport_NullTransport($app['swiftmailer.transport.eventdispatcher']);

        return $app;
    }

    public function testSendTest()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');

        $this->assertEquals("This is my subject", 1, "Subject is correct");
    }
}