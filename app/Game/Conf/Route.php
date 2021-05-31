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
namespace App\Game\Conf;

/**
 * 路由规则，key主要命令字=》array(子命令字对应策略类名)
 * 每条客户端对应的请求，路由到对应的逻辑处理类上处理
 * Class Route.
 */
class Route
{
    /**
     * websocket路由配置，websocke配置和tcp配置需要先去配置（MainCmd)主命令子和(SubCmdSys)子主命令字配置文件.
     * @var array
     */
    public static $cmd_map = [
        //系统请求
        MainCmd::CMD_SYS => [
            SubCmd::HEART_ASK_REQ => 'HeartAsk',
        ],
        //游戏请求
        MainCmd::CMD_GAME => [
            SubCmd::SUB_GAME_START_REQ => 'GameStart',
            SubCmd::SUB_GAME_CALL_REQ => 'GameCall',
            SubCmd::SUB_GAME_DOUBLE_REQ => 'GameDouble',
            SubCmd::SUB_GAME_OUT_CARD_REQ => 'GameOutCard',
            SubCmd::CHAT_MSG_REQ => 'ChatMsg',
            SubCmd::SUB_GAME_ROOM_CREATE => 'GameRoomCreate',
            SubCmd::SUB_GAME_ROOM_JOIN => 'GameRoomJoin',
        ],
    ];
}
