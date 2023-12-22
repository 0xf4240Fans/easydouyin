<?php

declare(strict_types=1);

namespace EasyDouYin\Web;

use Closure;
use EasyDouYin\Kernel\Contracts\Server as ServerInterface;
use EasyDouYin\Kernel\Exceptions\BadRequestException;
use EasyDouYin\Kernel\Exceptions\InvalidArgumentException;
use EasyDouYin\Kernel\Exceptions\RuntimeException;
use EasyDouYin\Kernel\HttpClient\RequestUtil;
use EasyDouYin\Kernel\ServerResponse;
use EasyDouYin\Kernel\Traits\InteractWithHandlers;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class Server implements ServerInterface
{
    use InteractWithHandlers;

    protected ServerRequestInterface $request;

    /**
     * @throws Throwable
     */
    public function __construct(
        ServerRequestInterface $request = null,
    ) {
        $this->request = $request ?? RequestUtil::createDefaultServerRequest();
    }

    /**
     * @throws InvalidArgumentException
     * @throws BadRequestException
     * @throws RuntimeException
     */
    public function serve(): ResponseInterface
    {
        if ((bool) ($str = $this->request->getQueryParams()['echostr'] ?? '')) {
            return new Response(200, [], $str);
        }

        $message = $this->getRequestMessage($this->request);

        $response = $this->handle(new Response(200, [], 'success'), $message);

        return ServerResponse::make($response);
    }

    /**
     * @throws Throwable
     */
    public function addMessageListener(string $type, callable|string $handler): static
    {
        $handler = $this->makeClosure($handler);
        $this->withHandler(
            function (Message $message, Closure $next) use ($type, $handler): mixed {
                return $message->MsgType === $type ? $handler($message, $next) : $next($message);
            }
        );

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function addEventListener(string $event, callable|string $handler): static
    {
        $handler = $this->makeClosure($handler);
        $this->withHandler(
            function (Message $message, Closure $next) use ($event, $handler): mixed {
                return $message->Event === $event ? $handler($message, $next) : $next($message);
            }
        );

        return $this;
    }

    /**
     * @throws BadRequestException
     */
    public function getRequestMessage(ServerRequestInterface $request = null): Message
    {
        return Message::createFromRequest($request ?? $this->request);
    }
}
