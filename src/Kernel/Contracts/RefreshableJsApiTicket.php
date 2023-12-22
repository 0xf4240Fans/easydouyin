<?php

namespace EasyDouYin\Kernel\Contracts;

interface RefreshableJsApiTicket extends JsApiTicket
{
    public function refreshTicket(): string;
}
