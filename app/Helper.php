<?php

use Swoole\Websocket\Frame;
use Hyperf\Server\ServerFactory;
use Hyperf\Utils\ApplicationContext;
use Swoole\WebSocket\Server as WebSocketServer;

if (!function_exists('container')) {
    function container()
    {
        return ApplicationContext::getContainer();
    }
}
if (!function_exists('redis')) {
    function redis()
    {
        return container()->get(\Redis::class);
    }
}
if (!function_exists('server')) {
    function server()
    {
        return container()->get(ServerFactory::class)->getServer()->getServer();
    }
}
if (!function_exists('frame')) {
    function frame()
    {
        return container()->get(Frame::class);
    }
}
if (!function_exists('websocket')) {
    function websocket()
    {
        return container()->get(WebSocketServer::class);
    }
}