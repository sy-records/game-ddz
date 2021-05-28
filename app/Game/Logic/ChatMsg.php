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

 class ChatMsg extends AStrategy
 {
     /**
      * 执行方法.
      */
     public function exec()
     {
         //原封不动发回去
//        $data = Packet::packFormat('OK', 0, $this->_params['data']);
//        $data = Packet::packEncode($data, MainCmd::CMD_GAME, SubCmd::CHAT_MSG_RESP);
//        return $data;

         $game_conf = config('game');
         /** @var \Redis $redis */
         $redis = redis();
         $user_room_key = sprintf($game_conf['user_room'], $this->_params['userinfo']['account']);
         $room_no = $redis->get($user_room_key);
         $user_room_data_key = sprintf($game_conf['user_room_data'], $room_no);
         $uinfo = $redis->hGet($user_room_data_key, 'uinfo');
         $uinfo = json_decode($uinfo, true);
         $binds = $fds = [];
         if (! empty($uinfo)) {
             foreach ($uinfo as $u) {
                 $binds[] = sprintf($game_conf['user_bind_key'], $u);
             }
             $fds = $redis->mget($binds);
         }
         $user = ['user' => $this->_params['userinfo']['account']];
         $msg_data = array_merge($user, $this->_params['data']);

         $fds[] = $fd = $this->_params['userinfo']['fd'];
         //分别发消息给三个人
         foreach ($fds as $fd) {
             $data = Packet::packFormat('OK', 0, $msg_data);
             $data = Packet::packEncode($data, MainCmd::CMD_GAME, SubCmd::CHAT_MSG_RESP);
             server()->push($fd, $data, WEBSOCKET_OPCODE_BINARY);
         }
     }
 }
