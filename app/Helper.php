<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\Server\ServerFactory;
use Hyperf\Utils\ApplicationContext;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

if (! function_exists('container')) {
    function container()
    {
        return ApplicationContext::getContainer();
    }
}
if (! function_exists('redis')) {
    function redis()
    {
        return container()->get(\Redis::class);
    }
}
if (! function_exists('server')) {
    function server()
    {
        return container()->get(ServerFactory::class)->getServer()->getServer();
    }
}
if (! function_exists('frame')) {
    function frame()
    {
        return container()->get(Frame::class);
    }
}
if (! function_exists('websocket')) {
    function websocket()
    {
        return container()->get(WebSocketServer::class);
    }
}
