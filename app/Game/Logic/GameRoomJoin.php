<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */
namespace App\Game\Logic;

use App\Game\Conf\MainCmd;
use App\Game\Conf\SubCmd;
use App\Game\Core\AStrategy;
use App\Game\Core\Packet;

/**
 *  进入房间.
 */
class GameRoomJoin extends AStrategy
{
    /**
     * 执行方法.
     */
    public function exec()
    {
        // 获取房间号
        $account = $this->_params['userinfo']['account'];
        $room_no = $this->getRoomNo($account);

        $serv = server();
        $redis = redis();
        $fd = $this->_params['userinfo']['fd'];
        if ($room_no) {
            // 有房间了
            $msg = [
                'status' => 'fail',
                'msg' => '已经有房间了，请耐心等待！',
            ];

            $data = Packet::packFormat('OK', 0, $msg);
            return Packet::packEncode($data, MainCmd::CMD_SYS, SubCmd::ENTER_ROOM_FAIL_RESP);
        }
        // 绑定房间对应关系
        $result = true;
        $room_no = $this->_params['data'];

        // 检查房间人数
        $room_user_list_key = sprintf($this->getGameConf('room_user_list'), $room_no);
        $room_length = $redis->scard($room_user_list_key);
        if ($room_length == 0 || $room_length >= 3) {
            // 人数超3人，不允许加入
            $result = false;
        } else {
            $res = $redis->sadd($room_user_list_key, $account);

            // 保存用户和房间的关系
            $user_key = sprintf($this->getGameConf('user_room'), $account);
            $redis->set($user_key, $room_no);
        }

        if (! $result) {
            // 房间人数已满3人
            $msg = [
                'status' => 'fail',
                'msg' => '房间不存在或者人数已满3人，请进入其他房间！',
            ];

            $data = Packet::packFormat('OK', 0, $msg);
            return Packet::packEncode($data, MainCmd::CMD_SYS, SubCmd::ENTER_ROOM_FAIL_RESP);
        }
        // 发消息给房间用户
        $game_conf = config('game');
        $user = ['user' => $account];
        $msg_data = [
            'user' => $account,
            'data' => '我进入了房间',
        ];
        $room_users = $redis->sRandMember($room_user_list_key, 3);
        $serv = server();
        foreach ($room_users as $roomUser) {
            $key = sprintf($game_conf['user_bind_key'], $roomUser);
            $tmpFd = $redis->get($key);
            if ($tmpFd) {
                $data = Packet::packFormat('OK', 0, $msg_data);
                $data = Packet::packEncode($data, MainCmd::CMD_GAME, SubCmd::CHAT_MSG_RESP);
                server()->push($tmpFd, $data, WEBSOCKET_OPCODE_BINARY);
            }
        }
        return 0;
    }
}
