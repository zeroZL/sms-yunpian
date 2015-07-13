<?php

namespace Huying\Sms\YunPian\Test;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Huying\Sms\Message;
use Huying\Sms\MessageStatus;
use Huying\Sms\YunPian\Provider;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructFailed()
    {
        $provider = new Provider([
            'apiKey' => '123',
        ]);
    }

    public function testGetName()
    {
        $provider = new Provider([
            'apiKey' => '123',
            'resource' => 'sms',
        ]);

        $this->assertEquals('YunPian', $provider->getName());
    }

    public function getParamsRight()
    {
        $httpClients = [];
        $providerOptions = [];
        $messages = [];

        if (getenv('API_KEY')) {
            $providerOptions[] = [
                'apiKey' => getenv('API_KEY'),
                'resource' => getenv('RESOURCE'),
            ];
            $httpClients[] = new HttpClient();
            $messages[] = Message::create()
                ->setRecipient(getenv('TELEPHONE'))
                ->setTemplateId(getenv('TEMPLATE_ID'))
                ->setData("#company#=云片网&#code#=2134");
        }

        $providerOptions[] = [
            'apiKey' => 'test_key',
            'resource' => 'test_resource',
        ];
        $mock = new MockHandler([
            new Response(200, [], '{"code":"0","msg":"ok","result":{"count":"1","fee":" 1","sid":"1097"}}'),
        ]);
        $handler = HandlerStack::create($mock);
        $httpClients[] = new HttpClient(['handler' => $handler]);
        $messages[] = Message::create()
            ->setRecipient('18800000000')
            ->setTemplateId('123456')
            ->setData("#company#=云片网&#code#=2134");

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

        if (getenv('API_KEY')) {
            $providerOptions[] = [
                'apiKey' => getenv('API_KEY'),
                'resource' => getenv('RESOURCE'),
            ];
            $httpClients[] = new HttpClient();
            $messages[] = Message::create()
                ->setRecipient(getenv('TELEPHONE'))
                ->setTemplateId('1')
                ->setData("#company#=云片网&#code#=2134");
        }

        $providerOptions[] = [
            'apiKey' => 'test_key',
            'resource' => 'test_resource',
        ];
        $mock = new MockHandler([
            new Response(200, [], '{"code":"1","msg":"请求参数缺失","detail":"补充必须传入的参数"}'),
        ]);
        $handler = HandlerStack::create($mock);
        $httpClients[] = new HttpClient(['handler' => $handler]);
        $messages[] = Message::create()
            ->setRecipient('18800000000')
            ->setTemplateId('123456')
            ->setData("#company#=云片网&#code#=2134");

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
