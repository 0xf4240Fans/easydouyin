<?php

declare(strict_types=1);

namespace EasyDouYin\Web\Contracts;

use EasyDouYin\Kernel\Contracts\AccessToken;
use EasyDouYin\Kernel\Contracts\Config;
use EasyDouYin\Kernel\Contracts\Server;
use EasyDouYin\Kernel\HttpClient\AccessTokenAwareClient;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

interface Application
{
    public function getAccount(): Account;

    public function getServer(): Server;

    public function getRequest(): ServerRequestInterface;

    public function getClient(): AccessTokenAwareClient;

    public function getHttpClient(): HttpClientInterface;

    public function getConfig(): Config;

    public function getAccessToken(): AccessToken;

    public function getCache(): CacheInterface;
}
