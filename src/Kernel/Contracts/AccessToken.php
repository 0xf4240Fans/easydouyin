<?php

declare(strict_types=1);

namespace EasyDouYin\Kernel\Contracts;

interface AccessToken
{
    public function getToken(): string;

    /**
     * @return array<string,string>
     */
    public function toQuery(): array;

    public function getUserAccessToken(String $code): array;
}
