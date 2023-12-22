<?php

declare(strict_types=1);

namespace EasyDouYin\Web\Contracts;

interface Account
{
    public function getClientKey(): string;

    public function getClientSecret(): string;
}
