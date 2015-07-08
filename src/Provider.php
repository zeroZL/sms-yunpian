<?php

namespace Huying\Sms\RongLian;

use GuzzleHttp\Psr7\Response;
use Huying\Sms\AbstractProvider;
use Huying\Sms\Message;
use Huying\Sms\ProviderException;

/**
 * 容联短信平台接口实现
 *
 * Class Provider
 */
class Provider extends AbstractProvider
{
    /**
     * Rest URL
     *
     * @var string
     */
    protected $restUrl = 'https://app.cloopen.com:8883';

    /**
     * 接口版本
     *
     * @var string
     */
    protected $softVersion = '2013-12-26';

    /**
     * 主账户 ID
     *
     * @var string
     */
    protected $accountSid;

    /**
     * 主账号授权令牌
     *
     * @var string
     */
    protected $authToken;

    /**
     * 应用 ID
     *
     * @var string
     */
    protected $appId;

    /**
     * 当前时间戳
     *
     * @var string
     */
    protected $timestamp;

    /**
     * 返回短信接口必须的参数
     * @param $key
     * @return array
     */
    protected function getRequiredOptions($key)
    {
        if ($key == self::PROVIDER_OPTIONS) {
            return [
                'accountSid',
                'authToken',
                'appId',
            ];
        } elseif ($key == self::MESSAGE_OPTIONS) {
            return [
                'data',
                'recipients',
                'template_id',
            ];
        } else {
            return []; // @codeCoverageIgnore
        }
    }

    /**
     * 获取接收使用的时间戳
     *
     * 每次调用返回值是相同的
     *
     * @return string
     */
    public function getTimestamp()
    {
        if ($this->timestamp) {
            return $this->timestamp;
        } else {
            return $this->timestamp = date('YmdHis');
        }
    }

    /**
     * 返回请求链接
     *
     * @param Message $message
     * @return string
     * @throws \RuntimeException
     */
    protected function getUrl(Message $message)
    {
        return $this->restUrl.'/'.$this->softVersion
            .'/Accounts/'.$this->accountSid
            .'/SMS/TemplateSMS?sig='.md5($this->accountSid.$this->authToken.$this->getTimestamp());
    }

    /**
     * 返回请求的方法
     *
     * @return string HTTP 方法
     */
    protected function getRequestMethod()
    {
        return static::METHOD_POST;
    }

    /**
     * 返回请求短信接口时的 headers
     *
     * @return array
     */
    protected function getRequestHeaders()
    {
        return [
            'Accept' => 'application/json;',
            'Content-Type' => 'application/json;charset=utf-8;',
            'Authorization' => base64_encode($this->accountSid.':'.$this->getTimestamp()),
        ];
    }

    /**
     * 返回请求短信接口时的 payload
     *
     * @param Message $message
     * @return string
     * @throws \RuntimeException
     */
    protected function getRequestPayload(Message $message)
    {
        $recipients = implode(',', $message->getRecipients());
        $templateId = (string) $message->getTemplateId();
        $data = $message->getData();
        array_walk($data, function (&$item) {
            $item = (string) $item;
        });
        $data = array_values($data);

        return json_encode([
            'to' => $recipients,
            'appId' => $this->appId,
            'templateId' => $templateId,
            'datas' => $data,
        ]);
    }

    /**
     * 处理短信接口的返回结果
     *
     * @param Response $response
     * @return array
     * @throws ProviderException
     */
    protected function handleResponse(Response $response)
    {
        $parsedResponse = self::parseJson($response->getBody());
        if ($parsedResponse['statusCode'] != '000000') {
            throw new ProviderException($parsedResponse['statusMsg'], $parsedResponse['statusCode'], $parsedResponse);
        }

        return $parsedResponse;
    }

    /**
     * 获取短信供应商名称
     *
     * @return string
     */
    public function getName()
    {
        return 'RongLian';
    }
}
