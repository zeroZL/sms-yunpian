<?php

namespace Huying\Sms\YunPian;

use GuzzleHttp\Psr7\Response;
use Huying\Sms\AbstractProvider;
use Huying\Sms\Message;
use Huying\Sms\ProviderException;
use GuzzleHttp\Exception\ClientException;

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
    protected $restUrl = 'http://yunpian.com';

    /**
     * 接口版本
     *
     * @var string
     */
    protected $softVersion = 'v1';

    /**
     *  用户唯一标识
     *  @var string
     */
    protected $apiKey;

    /**
     * 资源名 通常对应一类API
     *
     * @var string
     */
    protected $resource;

    /**
     * 为资源提供的操作方法
     *
     * @var string
     */
    protected $function='tpl_send';

    /**
     * 请求响应的结果格式
     *
     * @var string
     */
    protected $format='json';

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
                'apiKey',
                'resource',
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
            .'/'.$this->resource
            .'/'.$this->function.'.'.$this->format;
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
            'Accept' => 'application/json;charset=utf-8;',
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8;',
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
        $mobile = implode(',', $message->getRecipients());
        $templateId = (string) $message->getTemplateId();
        $templateValue = urlencode((string) $message->getData());
        return "apikey=$this->apiKey&tpl_id=$templateId&tpl_value=$templateValue&mobile=$mobile";
    }

    /**
     * 处理短信接口的返回结果
     *
     * @param $response
     * @return array
     * @throws ProviderException
     */
    protected function handleResponse($response)
    {
        if($response instanceof Response) {
            $parsedResponse = self::parseJson($response->getBody());
            var_dump($parsedResponse);
            if ($parsedResponse['code'] != 0) {
                throw new ProviderException($parsedResponse['msg'], $parsedResponse['code'], $parsedResponse);
            }
            return $parsedResponse;
        } elseif($response instanceof \GuzzleHttp\Exception\ClientException ) {
            $parsedResponse = self::parseJson($response->getResponse()->getBody());
            var_dump($parsedResponse);
                if ($parsedResponse != null&&$parsedResponse['code'] != 0) {
                    throw new ProviderException($parsedResponse['msg'], $parsedResponse['code'], $parsedResponse);
                }
                return $parsedResponse;
            }
    }

    /**
     * 获取短信供应商名称
     *
     * @return string
     */
    public function getName()
    {
        return 'YunPian';
    }
}
