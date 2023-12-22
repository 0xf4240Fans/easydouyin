<?php

declare(strict_types=1);

namespace EasyDouYin\Web;

use EasyDouYin\Web\Contracts\Account as AccountInterface;
use RuntimeException;

class Account implements AccountInterface
{
    public function __construct(
        protected string $clientKey,
        protected ?string $clientSecret
    ) {
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function getClientSecret(): string
    {
        if (null === $this->clientSecret) {
            throw new RuntimeException('No client Secret configured.');
        }

        return $this->clientSecret;
    }
}
