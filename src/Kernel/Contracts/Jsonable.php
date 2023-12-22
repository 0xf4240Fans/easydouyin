<?php

declare(strict_types=1);

namespace EasyDouYin\Kernel\Contracts;

interface Jsonable
{
    public function toJson(): string|false;
}
