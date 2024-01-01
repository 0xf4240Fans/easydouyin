# [EasyDouYin](https://github.com/0xf4240Fans/easydouyin)

📦 一个 PHP 抖音开发 SDK。

## 环境需求

- PHP >= 8.0.2
- [Composer](https://getcomposer.org/) >= 2.0

## 安装

```bash
composer require 0xf4240fans/easydouyin
```

## 使用示例

基本使用（以网站应用服务端为例）:

```php
<?php

use EasyDouYin\Web\Application;

$config = [
    'client_key' => 'awdb56xg27xocxxx',
    'client_secret' => '318bd5d26f2f27650a5e0d2de7c9fxxx'
];

$app = new Application($config);

// 获取热门视频榜单数据
$response = $app->getClient()->get("data/extern/billboard/hot_video/");

# 查看返回结果
var_dump($response->toArray());
```

## 文档和链接

[官网](https://www.0xf4240.fans) · [讨论](https://github.com/0xf4240Fans/easydouyin/discussions)

## License

MIT

## 感谢

💡 Inspired by [easywechat](https://easywechat.com/)