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
namespace App\Task;

use App\Game\Conf\MainCmd;
use App\Game\Conf\SubCmd;
use App\Game\Core\DdzPoker;
use App\Game\Core\Packet;
use Hyperf\Task\Annotation\Task;
use Psr\Container\ContainerInterface;

class GameSyncTask
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 游戏房间匹配.
     * @Task
     * @param mixed $fd
     * @param mixed $room_no
     */
    public function gameRoomMatch($fd, $room_no): void
    {
        $game_conf = config('game');
        $redis = redis();
        $room_user_list_key = sprintf($game_conf['room_user_list'], $room_no);
        $len = $redis->sCard($room_user_list_key);
        $serv = server();
        if (! empty($room_no) && $len == 3) {
            //匹配成功, 下发手牌数据, 并进入房间数据
            $users = $redis->sRandMember($room_user_list_key, 3);
            $users_key = $fds = [];
            foreach ($users as $account) {
                $key = sprintf($game_conf['user_bind_key'], $account);
                //根据账号获取fd
                $fds[$account] = $redis->get($key);
            }

            //随机发牌
            $obj = new DdzPoker();
            $card = $obj->dealCards($users);

            //存入用户信息
            $room_data = [
                'room_no' => $room_no,
                'hand' => $card['card']['hand'],
            ];
            foreach ($users as $k => $v) {
                $room_data['uinfo'][] = $v;
                $room_data[$v] = [
                    'card' => $card['card'][$v],
                    'chair_id' => ($k + 1),
                ];
            }
            $user_room_data_key = sprintf($game_conf['user_room_data'], $room_no);
            $this->arrToHashInRedis($room_data, $user_room_data_key);
            //分别发消息给三个人
            foreach ($users as $k => $v) {
                if (isset($fds[$v])) {
                    $data = Packet::packFormat('OK', 0, $room_data[$v]);
                    $data = Packet::packEncode($data, MainCmd::CMD_SYS, SubCmd::ENTER_ROOM_SUCC_RESP);
                    $serv->push((int) $fds[$v], $data, WEBSOCKET_OPCODE_BINARY);
                }
            }
        } else {
            //匹配失败， 请继续等待
            $msg = [
                'status' => 'fail',
                'msg' => '人数不够3人，请耐心等待!',
            ];
            $data = Packet::packFormat('OK', 0, $msg);
            $data = Packet::packEncode($data, MainCmd::CMD_SYS, SubCmd::ENTER_ROOM_FAIL_RESP);
            $serv->push((int) $fd, $data, WEBSOCKET_OPCODE_BINARY);
        }
    }

    /**
     * 广播叫地主.
     * @param $account
     * @param $calltype
     * @param $chair_id
     */
    public function gameCall($account, $calltype, $chair_id)
    {
        $fds = $this->_getRoomFds($account);
        //匹配失败， 请继续等待
        $msg = [
            'account' => $account,
            'calltype' => $calltype,
            'chair_id' => $chair_id,
            'calltime' => time(),
        ];
        $data = Packet::packFormat('OK', 0, $msg);
        $data = Packet::packEncode($data, MainCmd::CMD_GAME, SubCmd::SUB_GAME_CALL_TIPS_RESP);
        $serv = server();
        $this->pushToUsers($serv, $fds, $data);
    }

    /**
     * 当connetions属性无效时可以使用此方法，服务器广播消息， 此方法是给所有的连接客户端， 广播消息，通过方法getClientList广播.
     * @param $serv
     * @param $data
     * @return array
     */
    protected function pushToAll($serv, $data)
    {
        $client = [];
        $start_fd = 0;
        while (true) {
            $conn_list = $serv->getClientList($start_fd, 10);
            if ($conn_list === false or count($conn_list) === 0) {
                echo "BroadCast finish\n";
                break;
            }
            $start_fd = end($conn_list);
            foreach ($conn_list as $fd) {
                //获取客户端信息
                $client_info = $serv->getClientInfo((int) $fd);
                $client[$fd] = $client_info;
                if (isset($client_info['websocket_status']) && $client_info['websocket_status'] == 3) {
                    $serv->push((int) $fd, $data, WEBSOCKET_OPCODE_BINARY);
                }
            }
        }
        return $client;
    }

    /**
     * 对多用发送信息.
     * @param $serv
     * @param $users
     * @param $data
     */
    protected function pushToUsers($serv, $users, $data)
    {
        foreach ($users as $fd) {
            //获取客户端信息
            $client_info = $serv->getClientInfo((int) $fd);
            $client[$fd] = $client_info;
            if (isset($client_info['websocket_status']) && $client_info['websocket_status'] == 3) {
                $serv->push((int) $fd, $data, WEBSOCKET_OPCODE_BINARY);
            }
        }
    }

    /**
     * 把php数组存入redis的hash表中.
     * @param $arr
     * @param $hash_key
     */
    protected function arrToHashInRedis($arr, $hash_key)
    {
        foreach ($arr as $key => $val) {
            redis()->hSet((string) $hash_key, (string) $key, json_encode($val));
        }
    }

    /**
     * 通过accounts获取fds.
     * @param $account
     * @return array
     */
    private function _getRoomFds($account)
    {
        $game_conf = config('game');
        $user_room_data = $game_conf['user_room_data'];
        $redis = redis();
        $uinfo = $redis->hGet($user_room_data, $account);
        $uinfo = json_decode($uinfo, true);
        $accs = isset($uinfo['account']) ? $uinfo['account'] : [];
        $binds = $fds = [];
        if (! empty($accs)) {
            foreach ($accs as $v) {
                $binds[] = sprintf($game_conf['user_bind_key'], $v);
            }
            $fds = $redis->mget($binds);
        }
        return $fds;
    }
}
