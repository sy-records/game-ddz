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
 *  创建房间.
 */
class GameRoomCreate extends AStrategy
{
    /**
     * 执行方法.
     */
    public function exec()
    {
        // 获取用户房间
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
            return Packet::packEncode($data, MainCmd::CMD_SYS, SubCmd::CREATE_ROOM_FAIL_RESP);
        }
        // 没有房间，创建房间
        $room_no_key = $this->getGameConf('user_room_no');
        if ($redis->exists($room_no_key)) {
            $room_no = $redis->get($room_no_key);
            ++$room_no;
            $redis->set($room_no_key, $room_no);
        } else {
            $room_no = intval(1000001);
            $redis->set($room_no_key, $room_no);
        }

        // 保存用户和房间的关系
        $redis->set(sprintf($this->getGameConf('user_room'), $account), $room_no);

        // 保存房间队列
        $redis->sadd(sprintf($this->getGameConf('room_user_list'), $room_no), $account);

        //发消息给随机10个用户建立了新房间
        $msg_data = [
            'user' => $account,
            'data' => "我创建了新的房间[{$room_no}]",
        ];
        $users = $redis->sRandMember($this->getGameConf('room_list'), 10);
        foreach ($users as $account) {
            $key = sprintf($this->getGameConf('user_bind_key'), $account);

            //根据账号获取fd
            $tmpFd = $redis->get($key);
            if ($tmpFd) {
                $data = Packet::packFormat('OK', 0, $msg_data);
                $data = Packet::packEncode($data, MainCmd::CMD_GAME, SubCmd::CHAT_MSG_RESP);
                server()->push($tmpFd, $data, WEBSOCKET_OPCODE_BINARY);
            }
        }

        // 返回创建成功信息
        $msg = [
            'status' => 'succ',
            'msg' => "创建成功，房间号[{$room_no}]",
        ];
        $retData = Packet::packFormat('OK', 0, $msg);
        return Packet::packEncode($retData, MainCmd::CMD_SYS, SubCmd::CREATE_ROOM_SUCC_RESP);
    }
}
