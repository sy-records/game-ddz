<?php
namespace App\Game\Logic;

use App\Game\Core\AStrategy;
use App\Game\Core\Packet;
use App\Game\Conf\MainCmd;
use App\Game\Conf\SubCmd;

/**
 *  叫地主
 */

class GameCall extends AStrategy
{
    /**
     * 执行方法
     */
    public function exec()
    {
        $account = $this->_params['userinfo']['account'];
        $calltype = $this->_params['data']['type'];
        $user_room_data = $this->getRoomData($account);
        $room_user_data = json_decode($user_room_data[$account], true);
        //如果已经地主产生了, 直接下发叫地主信息
        if(isset($user_room_data['master']) && $user_room_data['last_chair_id']) {
            $this->callGameResp($room_user_data['chair_id'], $room_user_data['calltype'], $user_room_data['master'], $user_room_data['last_chair_id']);
        } else {
            if (!empty($room_user_data)) {
                if (!isset($room_user_data['calltype'])) {
                    $this->setRoomUserInfoDataByKey($room_user_data, $account, 'calltype', $calltype);
                } else {
                    $calltype = $room_user_data['calltype'];
                }
                $chair_id = $room_user_data['chair_id'];
                //广播叫地主消息
                $this->gameCallBroadcastResp($account, $calltype, $chair_id);
                //返回
                $this->callGameResp($chair_id, $calltype);
                //摸底牌操作
                $this->catchGameCardResp($account);
            }
        }
        return 0;
    }

    /**
     * 广播叫地主
     * @param $account
     * @param $calltype
     * @param $chair_id
     */
    public function gameCallBroadcastResp($account, $calltype, $chair_id)
    {
        $fds = $this->getRoomFds($account);
        //匹配失败， 请继续等待
        $msg = array('account'=>$account, 'calltype'=>$calltype, 'chair_id'=>$chair_id, 'calltime'=>time());
        $data = Packet::packFormat('OK', 0, $msg);
        $data = Packet::packEncode($data, MainCmd::CMD_GAME, SubCmd::SUB_GAME_CALL_TIPS_RESP);
        $this->pushToUsers($this->_params['serv'], $fds, $data);
    }

    /**
     * 组装抢地主返回数据
     * @param $chair_id
     * @param $calltype
     * @param $master
     * @param $last_chair_id
     * @return array|string
     */
    protected function callGameResp($chair_id, $calltype, $master = '', $last_chair_id = 0)
    {
        $msg = array('chair_id'=>$chair_id,'calltype'=>$calltype);
        if($master != '' && $last_chair_id > 0) {
            $msg['master'] = $master;
            $msg['last_chair_id'] = $last_chair_id;
        }
        $data = Packet::packFormat('OK', 0, $msg);
        $data = Packet::packEncode($data, MainCmd::CMD_GAME, SubCmd::SUB_GAME_CALL_RESP);
        $this->_params['serv']->push($this->_params['userinfo']['fd'], $data, WEBSOCKET_OPCODE_BINARY);
    }

    /**
     * 摸手牌操作
     * @param $account
     */
    protected function catchGameCardResp($account)
    {
        $room_data = $this->getRoomData($account);
        $infos = json_decode($room_data['uinfo'], true);
        if(!isset($room_data['master'])) {
            //加入游戏房间队列里面
            $calls = $accouts = array();
            $flag = 0;
            foreach ($infos as $v) {
                $u = json_decode($room_data[$v], true);
                if (isset($u['calltype'])) {
                    $flag++;
                    if ($u['calltype'] == 1) {
                        $calls[] = $v;
                    }
                }
            }
            if ($flag == 3) {
                //抢地主里随机一个人出来
                if (empty($calls)) {
                    $calls = $infos;
                }
                $key = array_rand($calls, 1);
                $user = $calls[$key];
                //抓牌,合并手牌数据
                $user_data = json_decode($room_data[$user], true);
                $hand = json_decode($room_data['hand'], true);
                $card = array_values(array_merge($user_data['card'], $hand));
                $card = $this->obj_ddz->_sortCardByGrade($card);
                $user_data['card'] = $card;
                //设置地主和用户手牌数据
                $param = array(
                    'master'=>$user,
                    $user=>json_encode($user_data)
                );
                $this->muitSetRoomData($account, $param);
                $this->catchGameCard($room_data, $user);
            }
        }
    }

    /**
     * 抓牌返回数据
     * @param $room_data
     * @param $user
     * @param $infos
     */
    protected function catchGameCard($room_data, $user)
    {
        $info = json_decode($room_data[$user], true);
        $msg = array(
            'user'=>$user,
            'chair_id' => $info['chair_id'],
            'hand_card' => $room_data['hand']
        );
        $data = Packet::packFormat('OK', 0, $msg);
        $data = Packet::packEncode($data, MainCmd::CMD_GAME, SubCmd::SUB_GAME_CATCH_BASECARD_RESP);
        $this->pushToUsers($this->_params['serv'], $this->getRoomFds($user), $data);
    }
}