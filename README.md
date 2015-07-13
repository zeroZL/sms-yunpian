# 容联云通讯短信发送包

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

本包实现了容联云通讯的短信发送功能。

## 安装

通过 Composer 安装

``` bash
$ composer require huying/sms-yunpian
```

## 使用方法

### 实例化短信平台类

```php
$provider = new Huying\Sms\YunPian\Provider([
    'apiKey' => 'xxxxx',
    'resource' => 'xxxxx',
]);
```

### 直接发送短信

```php
$message = Message::create()
    ->setRecipient('18800000000')
    ->setData([
        '#company#=信派科技&#code#=1234',
    ])->using($provider)
    ->send();
    
$message = Message::create([
    'recipient' => '18800000000',
    'data' => [
        '#company#=信派科技&#code#=1234',
    ],
])using($provider))->send();
```

### 判断短信是否发送成功

```php
if ($message->getStatus() == Huying\Sms\MessageStatus::STATUS_SENT) {
    echo '发送成功';
} else {
    echo '发送失败:错误码'.$message->getError()->getCode()
        .',错误消息:'.$message->getError()->getMessage();
}
```

## 更新日志

请访问 [更新日志](CHANGELOG.md) 查看有关该项目的更新信息。

## 贡献代码

请查看 [贡献指南](CONTRIBUTING.md)。

## 开发者

- [zero ZL][link-author]
- [所有贡献者][link-contributors]

## 许可协议

本项目使用 MIT 协议，详情请查看 [License File](LICENSE.md)。

[ico-version]: https://img.shields.io/packagist/v/huying/sms-yunpian.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/echo58/sms-yunpian/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/echo58/sms-yunpian.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/echo58/sms-yunpian.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/huying/sms-yunpian.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/huying/sms-yunpian
[link-travis]: https://travis-ci.org/echo58/sms-yunpian
[link-scrutinizer]: https://scrutinizer-ci.com/g/echo58/sms-yunpian/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/echo58/sms-yunpian
[link-downloads]: https://packagist.org/packages/huying/sms-yunpian
[link-author]: https://github.com/zeroZL
[link-contributors]: ../../contributors
