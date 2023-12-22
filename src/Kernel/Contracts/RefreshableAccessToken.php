<?php

namespace EasyDouYin\Kernel\Contracts;

interface RefreshableAccessToken extends AccessToken
{
    public function refresh(): string;
}
