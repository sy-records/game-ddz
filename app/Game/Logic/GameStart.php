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
use App\Task\GameSyncTask;

/**
 *  游戏开始.
 */
class GameStart extends AStrategy
{
    /**
     * 执行方法.
     */
    public function exec()
    {
        //加入游戏房间队列里面
        $account = $this->_params['userinfo']['account'];
        $room_data = $this->getRoomData($account);
        $user_room_data = isset($room_data[$account]) ? json_decode($room_data[$account], true) : [];
        if ($user_room_data) {
            //是否产生地主
            $master = isset($room_data['master']) ? $room_data['master'] : '';
            if ($master) {
                $user_room_data['is_master'] = 1;
                if ($master == $account) {
                    //此人是地主
                    $user_room_data['master'] = 1;
                }
            } else {
                $user_room_data['is_master'] = 0;
            }

            //轮到谁出牌了
            $last_chair_id = isset($room_data['last_chair_id']) ? $room_data['last_chair_id'] : 0;
            $next_chair_id = isset($room_data['next_chair_id']) ? $room_data['next_chair_id'] : 0;
            $user_room_data['is_first_round'] = false;
            if ($next_chair_id > 0) {
                $user_room_data['index_chair_id'] = $next_chair_id;
                if ($next_chair_id == $last_chair_id) {
                    //首轮出牌
                    $user_room_data['is_first_round'] = true;
                }
            } else {
                //地主首次出牌
                if (isset($room_data[$master])) {
                    $master_info = json_decode($room_data[$master], true);
                    $user_room_data['index_chair_id'] = $master_info['chair_id'];
                    //首轮出牌
                    $user_room_data['is_first_round'] = true;
                }
            }

            //判断游戏是否结束
            $user_room_data['is_game_over'] = isset($room_data['is_game_over']) ? $room_data['is_game_over'] : false;
            //进入房间成功
            $msg = $user_room_data;
            $room_data = Packet::packFormat('OK', 0, $msg);
            return Packet::packEncode($room_data, MainCmd::CMD_SYS, SubCmd::ENTER_ROOM_SUCC_RESP);
        }
        //$room_list = $this->getGameConf('room_list');
        //if($room_list) {
        //判断是否在队列里面
        //redis()->sAdd($room_list, $this->_params['userinfo']['account']);
        //投递异步任务
        //$task = container()->get(GameSyncTask::class);
        //$task->gameRoomMatch($this->_params['userinfo']['fd']);
        //}

        $room_no = $this->getRoomNo($account);
        if ($room_no) {
            $task = container()->get(GameSyncTask::class);
            $task->gameRoomMatch($this->_params['userinfo']['fd'], $room_no);
        } else {
            // 没有进入房间的放到公共队列里
            $room_list = $this->getGameConf('room_list');
            if ($room_list) {
                redis()->sAdd($room_list, $this->_params['userinfo']['account']);
            }

            //未加入房间
            $msg = [
                'status' => 'fail',
                'msg' => '您还未加入房间，请创建房间或者输入房间号进入房间!',
            ];
            $data = Packet::packFormat('OK', 0, $msg);
            $data = Packet::packEncode($data, MainCmd::CMD_SYS, SubCmd::ENTER_ROOM_FAIL_RESP);
            $serv = server();
            $serv->push($this->_params['userinfo']['fd'], $data, WEBSOCKET_OPCODE_BINARY);
        }
        return 0;
    }
}
