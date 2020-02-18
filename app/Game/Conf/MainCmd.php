<?php
namespace App\Game\Conf;

/**
 * 主命令字定义，（可以有多个主命令字，每个主命令字对应一个子命令字）
 * Class MainCmd
 * @package App\Game\Conf
 */
class MainCmd
{
    const CMD_SYS = 1;      //websocket系统主命令字，（主命令字）- 客户端使用
    const CMD_GAME = 2;     //游戏协议系统主命令字，（主命令字）- 客户端使用
};
