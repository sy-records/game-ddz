<?php
namespace App\Game\Logic;

use App\Game\Core\AStrategy;
use App\Game\Core\Packet;
use App\Game\Conf\MainCmd;
use App\Game\Conf\SubCmd;

use App\Log;

/**
 *  打牌响应
 */

class GameOutCard extends AStrategy
{
    /**
     * 执行方法
     */
    public function exec()
    {
        $account = $this->_params['userinfo']['account'];
        $user_room_data = $this->getRoomData($account);
        $out_cards = $this->_params['data'];
        $ret = $this->playCard($user_room_data, $out_cards, $account);
        return $ret;
    }

    /**
     * 用户打牌逻辑处理
     * @param $user_room_data
     * @param $out_cards
     * @param $account
     * @return int
     */
    protected function playCard($user_room_data, $out_cards, $account)
    {
        //轮次
        $round = isset($user_room_data['round']) ?  $user_room_data['round'] + 1 : 0;
        //手次
        $hand = isset($user_room_data['hand_num']) ?  $user_room_data['hand_num'] + 1 : 1;
        //本轮次上一次牌型
        $last_chair_id = isset($user_room_data['last_chair_id']) ?  $user_room_data['last_chair_id'] : 0;
        //本轮次上一次牌型
        $last_card_type = isset($user_room_data['last_card_type']) ?  $user_room_data['last_card_type'] : 0;
        //本轮次上一次牌值
        $last_card = isset($user_room_data['last_card']) ?  $user_room_data['last_card'] : '';
        //下一个出牌人椅子id
        $next_chair_id = $out_cards['chair_id'] + 1;
        $next_chair_id = ($next_chair_id > 3) ? $next_chair_id - 3 : $next_chair_id;

        //根据椅子查询手牌信息
        $my_card = json_decode($user_room_data[$account], true);

        //出牌牌型
        $card_type = '无';

        //验证出牌数据
        if($out_cards['status'] == 1) {
            if(count($out_cards['card']) == 0) {
                return $this->gameOutCard(array('status'=>1, 'msg'=>'出牌非法, 请出手牌'));
            } else {
                //判断手牌是否存在, 手牌存在继续往下执行
                if (!$out_cards['card'] == array_intersect($out_cards['card'], $my_card['card'])) {
                    return $this->gameOutCard(array('status'=>2, 'msg'=>'出牌非法, 出牌数据有问题'));
                }
                //检查牌型
                $arr = $this->obj_ddz->checkCardType($out_cards['card']);
                if($arr['type'] == 0) {
                    return $this->gameOutCard(array('status'=>3, 'msg'=>'出牌非法, 牌型有误'));
                } else {
                    $card_type = $arr['type_msg'];
                }
                //如果非首轮牌, 请验证牌型, 并判断牌型是否一直, 如果打出的牌型是, 炸弹和飞机, 跳过验证, 13表示炸弹,14表示飞机
                if($last_card_type > 0 && !in_array($arr['type'], array(13,14)) && $last_card_type != $arr['type']) {
                    return $this->gameOutCard(array('status'=>3, 'msg'=>'出牌非法, 和上一把牌型不符合'));
                }
                $out_cards['card_type'] = $arr['type'];
                //比牌大小
                if(!$this->obj_ddz->checkCardSize($out_cards['card'], json_decode($last_card, true))) {
                    return $this->gameOutCard(array('status'=>4, 'msg'=>'出牌非法, 牌没有大过上家牌'));
                }
            }
        } else {
            //过牌要验证是否为首次出牌, 如果是首次出牌是不能过牌的
            if($hand == 1 || $last_chair_id == $out_cards['chair_id']) {
                return $this->gameOutCard(array('status'=>4, 'msg'=>'出牌非法, 首次出牌不能过牌操作'));
            }
        }
        if($out_cards['chair_id'] < 1) {
            return $this->gameOutCard(array('status'=>5, 'msg'=>'出牌非法, 椅子ID非法'));
        }
        //判断游戏是否结束
        if(count($my_card['card']) < 1) {
            return $this->gameOutCard(array('status'=>6, 'msg'=>'游戏结束, 所有手牌已经出完'));
        }

        //出牌逻辑
        if($last_card_type == 0) {
            //如果上一次牌型为0, 证明没有牌型, 这次手牌为开始手牌
            $ret = $this->roundStart($user_room_data, $out_cards, $account, $hand, $next_chair_id);
            \App\Game\Core\Log::show($account.":第".$ret['round'].'回合-开始');
        } elseif($out_cards['status'] == 0 && $last_chair_id == $next_chair_id) {
            //上一轮过牌, 并上一次椅子id和这一次相等, 轮次结束
            $this->roundEnd($account, $last_chair_id, $hand, $next_chair_id);
            \App\Game\Core\Log::show($account.":第".$round.'回合-结束');
        } else {
            //跟牌逻辑
            $this->roundFollow($out_cards, $account, $hand, $next_chair_id);
            $last_chair_id = $out_cards['chair_id'];
            \App\Game\Core\Log::show($account.":第".$round.'回合-跟牌');
        }

        //判断下个用户, 是首次出牌还是跟牌操作
        $is_first_round = $last_chair_id == $next_chair_id ? true : false;
        //设置减少手牌数据
        $my_card = $this->setMyCard($user_room_data, $out_cards, $account);
        //判断游戏是否结束
        $is_game_over = (count($my_card['card']) < 1) ? true : false;
        //计算下家牌是否能大过上一手牌
//        $next_card = $this->findCardsByChairId($user_room_data, $next_chair_id);
//        $prv_card =  (isset($out_cards['card']) && count($out_cards['card']) > 0) ? $out_cards['card'] : json_decode($last_card, true);
//        $is_out_card = $this->obj_ddz->isPlayCard($next_card, $prv_card);
//        var_dump($next_card, $prv_card, $is_out_card);

        //并下发出牌提示
        $step = array(
            'round'=>$round,       //轮次
            'hand_num'=>$hand, //首次
            'chair_id'=>$out_cards['chair_id'], //出牌椅子
            'account'=>$account,                //出牌账号
            'show_type'=>$out_cards['status'], //1,跟牌, 2, 过牌
            'next_chair_id'=>$next_chair_id,   //下一个出牌的椅子id
            'is_first_round'=>$is_first_round,  //是否为首轮, 下一个出牌人的情况
            'card'=>$out_cards['card'],        //本次出牌
            'card_type' => $card_type,          //显示牌型
            'last_card'=>json_decode($last_card, true),          //上次最大牌
            'is_game_over' => $is_game_over        //游戏是否结束
        );


        // 如果游戏结束，构造游戏结果
        if ($is_game_over) {
            // 获取当前玩家身份
            $master = $user_room_data['master'] ?? '';
            $step['result'] = $account . ":1";
            $is_master = ($master == $account) ? 1 : 0;
            $user_info = json_decode($user_room_data['uinfo'], true);
            
            // 获取所有玩家
            foreach ($user_info as $user_account) {
                if ($account != $user_account) {
                    if ($master == $user_account) {
                        $step['result'] .= " {$user_account}:0";
                    } else {
                        $step['result'] .= " {$user_account}:" . ($is_master ? 0 : 1);
                    }
                }
            }
        }

        //记录一下出牌数据, 记录没步骤录像数据
        $this->setRoomPlayCardStep($account, 'step_'.$hand, json_encode($step));
        //广播打牌结果
        $ret =  $this->gameOutCardResp($this->_params['serv'], $account, $step);
        //游戏结束, 重置游戏数据
        $this->gameOver($account, json_decode($user_room_data['uinfo'], true), $is_game_over);
        //记录步骤信息
        Log::get()->info(json_encode($step));
        return $ret;
    }

    /**
     * 轮次开始
     * @param $user_room_data
     * @param $out_cards
     * @param $account
     * @param $hand
     * @param $next_chair_id
     * @return array
     */
    protected function roundStart($user_room_data, $out_cards, $account, $hand, $next_chair_id)
    {
        //当前轮次
        $round = isset($user_room_data['round']) ?  $user_room_data['round'] + 1 : 1;
        //本轮次开始时椅子id
        $start_chair_id = $out_cards['chair_id'];
        //本轮次最大牌椅子id
        $last_chair_id = $out_cards['chair_id'];
        //本轮次最大牌椅子i牌型
        $last_card_type = $out_cards['card_type'];
        //本轮次最大牌椅子牌值
        $last_card = $out_cards['card'];

        //结果存入redis
        $param = array(
            'round'=>$round,
            'hand_num'=>$hand,
            'start_chair_id'=>$start_chair_id,
            'last_chair_id'=>$last_chair_id,
            'last_card_type'=>$last_card_type,
            'last_card'=>json_encode($last_card),
            'next_chair_id'=>$next_chair_id
        );
        $this->muitSetRoomData($account, $param);
        return $param;
    }

    /**
     * 轮次结束
     * @param $account
     * @param $last_chair_id
     * @param $next_chair_id
     * @param $hand
     */
    protected function roundEnd($account, $last_chair_id, $hand, $next_chair_id)
    {
        //结果存入redis
        $param = array(
            'start_chair_id'=>$last_chair_id,
            'last_card_type'=>0,
            'last_card'=>json_encode(array()),
            'hand_num'=>$hand,
            'next_chair_id'=>$next_chair_id
        );
        $this->muitSetRoomData($account, $param);
    }

    /**
     * 跟牌
     * @param $out_cards
     * @param $account
     * @param $next_chair_id
     * @param $hand
     */
    protected function roundFollow($out_cards, $account, $hand, $next_chair_id)
    {
        //跟牌
        $param = array();
        if($out_cards['status'] == 1) {
            //本轮次上一次最大牌椅子id
            $param = array(
                'last_chair_id'=>$out_cards['chair_id'],
                'last_card'=>json_encode($out_cards['card']),
            );
        }
        $param['next_chair_id'] = $next_chair_id;
        $param['hand_num'] = $hand;
        //结果存入redis
        $this->muitSetRoomData($account, $param);
    }

    /**
     * 游戏结束
     * @param $account
     * @param $uinfo
     * @param $is_game_over
     */
    protected function gameOver($account, $uinfo, $is_game_over) : void {
        if($is_game_over) {
            //设置游戏结束标识
            $this->setRoomData($account, 'is_game_over', $is_game_over);

            // 清除房间队列
            $room_no = $this->getRoomNo($account);  
            $key = sprintf($this->getGameConf('room_user_list'), $room_no);;
            redis()->del($key);

            //清除数据, 进行下一轮玩牌, 随机分配
            $this->clearRoomNo($uinfo);
        }
    }

    /**
     * 设置我的手牌
     * @param $user_room_data
     * @param $cards
     * @param $account
     * @return mixed
     */
    protected function setMyCard($user_room_data, $cards, $account)
    {
        //根据椅子查询手牌信息
        $my_card = json_decode($user_room_data[$account], true);
        $hand_card = array_unique(array_values(array_diff($my_card['card'], $cards['card'])));
        if(isset($my_card['out_card'])) {
            $out_card = array_unique(array_values(array_merge($my_card['out_card'], $cards['card'])));
        } else {
            $out_card = $cards['card'];
        }
        $my_card['card'] = $hand_card;
        $my_card['out_card'] = $out_card;
        //写会redis
        $this->setRoomData($account, $account, json_encode($my_card));
        return $my_card;
    }

    /**
     * 根据椅子id找出这个一直用户的手牌
     * @param $user_room_data
     * @param $chair_id
     * @return array
     */
    protected function findCardsByChairId($user_room_data, $chair_id) {
        $uinfo = json_decode($user_room_data['uinfo'], true);
        $cards = array();
        foreach($uinfo as $v) {
            $d = json_decode($user_room_data[$v], true);
            if(isset($d['chair_id']) && $d['chair_id'] == $chair_id) {
                $cards = $d['card'];
                break;
            }
        }
        return $cards;
    }

    /**
     * 向客户端发送出牌提示响应, 单发
     * @param $param
     * @return array|string
     */
    protected function gameOutCard($param)
    {
        $data = Packet::packFormat('OK', 0, $param);
        $data = Packet::packEncode($data, MainCmd::CMD_GAME, SubCmd::SUB_GAME_OUT_CARD);
        return $data;
    }

    /**
     * 向客户端广播出牌响应, 群发
     * @param $serv
     * @param $account
     * @param $param
     * @return int
     */
    protected function gameOutCardResp($serv, $account, $param)
    {
        $data = Packet::packFormat('OK', 0, $param);
        $data = Packet::packEncode($data, MainCmd::CMD_GAME, SubCmd::SUB_GAME_OUT_CARD_RESP);
        $this->pushToUsers($serv, $this->getRoomFds($account), $data);
        //并提示成功
        return $this->gameOutCard(array('status'=>0, 'msg'=>'出牌成功', 'data'=>$param));
    }
}