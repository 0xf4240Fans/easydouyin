<?php

declare(strict_types=1);

namespace EasyDouYin\Web;

use EasyDouYin\Web\Contracts\Application as ApplicationInterface;
use EasyDouYin\Kernel\Traits\InteractWithCache;
use EasyDouYin\Kernel\Traits\InteractWithClient;
use EasyDouYin\Kernel\Traits\InteractWithConfig;
use EasyDouYin\Kernel\Traits\InteractWithHttpClient;
use EasyDouYin\Kernel\Traits\InteractWithServerRequest;
use EasyDouYin\Kernel\Contracts\Server as ServerInterface;
use EasyDouYin\Web\Contracts\Account as AccountInterface;
use EasyDouYin\Kernel\Contracts\AccessToken as AccessTokenInterface;
use EasyDouYin\Kernel\Contracts\RefreshableAccessToken as RefreshableAccessTokenInterface;
use EasyDouYin\Kernel\Contracts\JsApiTicket as JsApiTicketInterface;
use EasyDouYin\Kernel\HttpClient\AccessTokenAwareClient;
use EasyDouYin\Kernel\HttpClient\AccessTokenExpiredRetryStrategy;
use EasyDouYin\Kernel\HttpClient\RequestUtil;
use EasyDouYin\Kernel\HttpClient\Response;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpClient\Response\AsyncContext;
use Symfony\Component\HttpClient\RetryableHttpClient;

class Application implements ApplicationInterface
{
    use InteractWithConfig;
    use InteractWithCache;
    use InteractWithServerRequest;
    use InteractWithHttpClient;
    use InteractWithClient;
    use LoggerAwareTrait;

    protected ?ServerInterface $server = null;

    protected ?AccountInterface $account = null;

    protected AccessTokenInterface|RefreshableAccessTokenInterface|null $accessToken = null;

    protected ?JsApiTicketInterface $ticket = null;

    public function getAccount(): AccountInterface
    {
        if (! $this->account) {
            $this->account = new Account(
                clientKey: (string) $this->config->get('client_key'), /** @phpstan-ignore-line */
                clientSecret: (string) $this->config->get('client_secret'), /** @phpstan-ignore-line */
            );
        }

        return $this->account;
    }

    public function setAccount(AccountInterface $account): static
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @throws \ReflectionException
     * @throws InvalidArgumentException
     * @throws \Throwable
     */
    public function getServer(): Server|ServerInterface
    {
        if (! $this->server) {
            $this->server = new Server(
                request: $this->getRequest()
            );
        }

        return $this->server;
    }

    public function setServer(ServerInterface $server): static
    {
        $this->server = $server;

        return $this;
    }

    public function getAccessToken(): AccessTokenInterface|RefreshableAccessTokenInterface
    {
        if (! $this->accessToken) {
            $this->accessToken = new AccessToken(
                clientKey: $this->getAccount()->getClientKey(),
                clientSecret: $this->getAccount()->getClientSecret(),
                cache: $this->getCache(),
                httpClient: $this->getHttpClient()
            );
        }

        return $this->accessToken;
    }

    public function setAccessToken(AccessTokenInterface|RefreshableAccessTokenInterface $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function createClient(): AccessTokenAwareClient
    {
        $httpClient = $this->getHttpClient();

        if ((bool) $this->config->get('http.retry', false)) {
            $httpClient = new RetryableHttpClient(
                $httpClient,
                $this->getRetryStrategy(),
                (int) $this->config->get('http.max_retries', 2) // @phpstan-ignore-line
            );
        }

        return (new AccessTokenAwareClient(
            client: $httpClient,
            accessToken: $this->getAccessToken(),
            failureJudge: fn (Response $response) => (bool) ($response->toArray()['errcode'] ?? 0),
            throw: (bool) $this->config->get('http.throw', true),
        ))->setPresets($this->config->all());
    }

    public function getRetryStrategy(): AccessTokenExpiredRetryStrategy
    {
        $retryConfig = RequestUtil::mergeDefaultRetryOptions((array) $this->config->get('http.retry', []));

        return (new AccessTokenExpiredRetryStrategy($retryConfig))
            ->decideUsing(function (AsyncContext $context, ?string $responseContent): bool {
                return ! empty($responseContent)
                    && str_contains($responseContent, '10020');  // 频控规则：5 分钟内超过 500 次接口调用，接口报错，错误码 10020
            });
    }

    /**
     * @return array<string,mixed>
     */
    protected function getHttpClientDefaultOptions(): array
    {
        return array_merge(
            ['base_uri' => 'https://open.douyin.com/'],
            (array) $this->config->get('http', [])
        );
    }
}
