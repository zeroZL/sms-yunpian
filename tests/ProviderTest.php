<?php

namespace Huying\Sms\RongLian\Test;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Huying\Sms\Message;
use Huying\Sms\MessageStatus;
use Huying\Sms\RongLian\Provider;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructFailed()
    {
        $provider = new Provider([
            'accountSid' => '123',
            'authToken' => '123',
        ]);
    }

    public function testGetName()
    {
        $provider = new Provider([
            'accountSid' => '123',
            'authToken' => '123',
            'appId' => '123',
        ]);

        $this->assertEquals('RongLian', $provider->getName());
    }

    public function getParamsRight()
    {
        $httpClients = [];
        $providerOptions = [];
        $messages = [];

        if (getenv('ACCOUNT_SID')) {
            $providerOptions[] = [
                'accountSid' => getenv('ACCOUNT_SID'),
                'authToken' => getenv('AUTH_TOKEN'),
                'appId' => getenv('APP_ID'),
            ];
            $httpClients[] = new HttpClient();
            $messages[] = Message::create()
                ->setRecipient(getenv('TELEPHONE'))
                ->setTemplateId(getenv('TEMPLATE_ID'))
                ->setData([
                    rand(100000, 999999),
                    rand(1, 100),
                ]);
        }

        $providerOptions[] = [
            'accountSid' => 'test_sid',
            'authToken' => 'test_token',
            'appId' => 'test_app_id',
        ];
        $mock = new MockHandler([
            new Response(200, [], '{"statusCode":"000000","TemplateSMS":{"dateCreated":"20130201155306","smsMessageSid":" ff8080813c373cab013c94b0f0512345"}}'),
        ]);
        $handler = HandlerStack::create($mock);
        $httpClients[] = new HttpClient(['handler' => $handler]);
        $messages[] = Message::create()
            ->setRecipient('18800000000')
            ->setTemplateId('123456')
            ->setData([
                '4585',
                '15'
            ]);

        return array_map(function ($options, $httpClient, $message) {
            return [$options, $httpClient, $message];
        }, $providerOptions, $httpClients, $messages);
    }

    /**
     * @dataProvider getParamsRight
     */
    public function testProviderRight($options, HttpClient $httpClient, Message $message)
    {
        $provider = new Provider($options, [
            'httpClient' => $httpClient,
        ]);
        $message->using($provider)->send();

        $this->assertEquals(MessageStatus::STATUS_SENT, $message->getStatus());
    }

    public function getParamsWrong()
    {
        $httpClients = [];
        $providerOptions = [];
        $messages = [];

        if (getenv('ACCOUNT_SID')) {
            $providerOptions[] = [
                'accountSid' => getenv('ACCOUNT_SID'),
                'authToken' => getenv('AUTH_TOKEN'),
                'appId' => getenv('APP_ID'),
            ];
            $httpClients[] = new HttpClient();
            $messages[] = Message::create()
                ->setRecipient(getenv('TELEPHONE'))
                ->setTemplateId('xxxx')
                ->setData([
                    rand(100000, 999999),
                    rand(1, 100),
                ]);
        }

        $providerOptions[] = [
            'accountSid' => 'test_sid',
            'authToken' => 'test_token',
            'appId' => 'test_app_id',
        ];
        $mock = new MockHandler([
            new Response(200, [], '{"statusCode":"000001","statusMsg":"you are wrong, boy!"}'),
        ]);
        $handler = HandlerStack::create($mock);
        $httpClients[] = new HttpClient(['handler' => $handler]);
        $messages[] = Message::create()
            ->setRecipient('18800000000')
            ->setTemplateId('123456')
            ->setData([
                '4585',
                '15'
            ]);

        return array_map(function ($options, $httpClient, $message) {
            return [$options, $httpClient, $message];
        }, $providerOptions, $httpClients, $messages);
    }

    /**
     * @dataProvider getParamsWrong
     */
    public function testProviderWrong($options, HttpClient $httpClient, Message $message)
    {
        $provider = new Provider($options, [
            'httpClient' => $httpClient,
        ]);
        $message->using($provider)->send();

        $this->assertEquals(MessageStatus::STATUS_FAILED, $message->getStatus());
    }
}
