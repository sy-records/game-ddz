<?php
namespace App\Game\Core;

use App\Game\Core\DdzPoker;


/**
 *  游戏策略静态类，规范游戏策略类，此类可以扩展每个策略公共的方法
 *  每个游戏请求逻辑，都算一个游戏策略， 采用策略模式实现
 *  策略支持三种协议的策略， WEBSOCKET, HTTP, TCP, 分别在app目录下
 *  框架的开发也是主要分别实现每种协议逻辑，根据路由配置转发到相对应的策略里
 */
  
 abstract class AStrategy
 {
    /**
     * 参数
     */         
    protected $_params = array();

     /**
      * 斗地主对象
      * @var null
      */
    protected $obj_ddz = null;

     /**
      * 构造函数，协议传输过来的数据
      * AStrategy constructor.
      * @param array $params
      */
    public function __construct($params = array()) {
        $this->_params = $params;
        $this->obj_ddz = new DdzPoker();
    }
    
    /**
     * 执行方法，每条游戏协议，实现这个方法就行
     */         
    abstract public function exec();

     /**
      * 服务器广播消息， 此方法是给所有的连接客户端， 广播消息
      * @param $serv
      * @param $data
      */
    protected function Broadcast($serv, $data)
    {
        foreach($serv->connections as $fd) {
        	$serv->push($fd, $data, WEBSOCKET_OPCODE_BINARY);
        } 
    }

     /**
      * 当connetions属性无效时可以使用此方法，服务器广播消息， 此方法是给所有的连接客户端， 广播消息，通过方法getClientList广播
      * @param $serv
      * @param $data
      */
    protected function BroadCast2($serv, $data)
    {
        $start_fd = 0;
        while(true) {
            $conn_list = $serv->getClientList($start_fd, 10);
            if ($conn_list===false or count($conn_list) === 0) {
                Log::show("BroadCast finish");
                break;
            }
            $start_fd = end($conn_list);
            foreach($conn_list as $fd) {
                //获取客户端信息
                $client_info = $serv->getClientInfo($fd);
                if(isset($client_info['websocket_status']) && $client_info['websocket_status'] == 3) {
                    $serv->push($fd, $data, WEBSOCKET_OPCODE_BINARY);
                }
            }
        }
    }

     /**
      * 对多用发送信息
      * @param $serv
      * @param $users
      * @param $data
      */
     protected function pushToUsers($serv, $users, $data)
     {
         foreach($users as $fd) {
             //获取客户端信息
             $client_info = $serv->getClientInfo($fd);
             $client[$fd] = $client_info;
             if(isset($client_info['websocket_status']) && $client_info['websocket_status'] == 3) {
                 $serv->push($fd, $data, WEBSOCKET_OPCODE_BINARY);
             }
         }
     }

     /**
      * 获取房间信息
      * @param $account
      * @return array
      */
     protected function getRoomData($account)
     {
         $user_room_data = array();
         //获取用户房间号
         $room_no = $this->getRoomNo($account);
         //房间信息
         $game_key = $this->getGameConf('user_room_data');
         if($game_key) {
             $user_room_key = sprintf($game_key, $room_no);
             $user_room_data = redis()->hGetAll($user_room_key);
         }
         return $user_room_data;
     }

     /**
      * 获取房间信息通过key
      * @param $account
      * @param $key
      * @return mixed
      */
     protected function getRoomDataByKey($account, $key)
     {
         $data = array();
         $no = $this->getRoomNo($account);
         $game_key = $this->getGameConf('user_room_data');
         if($no && $game_key) {
             $user_room_key = sprintf($game_key, $no);
             $user_room_data = redis()->hGet($user_room_key, $key);
             $data = json_decode($user_room_data, true);
             if (is_null($data)) {
                 $data = $user_room_data;
             }
         }
         return $data;
     }

     /**
      * 获取用户房间号
      * @param $account
      * @return mixed
      */
     protected function getRoomNo($account)
     {
         $game_key = $this->getGameConf('user_room');
         //获取用户房间号
         $room_key = sprintf($game_key, $account);
         $room_no = redis()->get($room_key);
         return $room_no ? $room_no : 0;
     }

     /**
      * 获取房间信息通过key
      * @param $account
      * @return mixed
      */
     protected function getRoomUserInfoDataByKey($account)
     {
         $user_data = array();
         $no = $this->getRoomNo($account);
         $game_key = $this->getGameConf('user_room_data');
         if($no && $game_key) {
             //房间信息
             $user_room_key = sprintf($game_key, $no);
             $user_data = redis()->hGet($user_room_key, $account);
             $user_data = json_decode($user_data, true);
         }
         return $user_data;
     }

     /**
      * 设置房间用户玩牌信息
      * @param $account
      * @param $key
      * @param $value
      */
     protected function setRoomData($account, $key, $value)
     {
         $no = $this->getRoomNo($account);
         $game_key = $this->getGameConf('user_room_data');
         if($no && $game_key) {
             $user_room_key = sprintf($game_key, $no);
             redis()->hSet((string)$user_room_key, (string)$key, $value);
         }
     }

     /**
      * 批量设置房间信息
      * @param $account
      * @param $params
      */
     protected function muitSetRoomData($account, $params)
     {
         $no = $this->getRoomNo($account);
         $game_key = $this->getGameConf('user_room_data');
         if($no && $game_key) {
             $user_room_key = sprintf($game_key, $no);
             redis()->hMSet($user_room_key, $params);
         }
     }

     /**
      * 设置房间信息
      * @param $room_user_data
      * @param $account
      * @param $key
      * @param $value
      */
     protected function setRoomUserInfoDataByKey($room_user_data, $account, $key, $value)
     {
         $no = $this->getRoomNo($account);
         $game_key = $this->getGameConf('user_room_data');
         if($no && $game_key) {
             $room_user_data[$key] = $value;
             $user_room_key = sprintf($game_key, $no);
             redis()->hSet((string)$user_room_key, (string)$account, json_encode($room_user_data));
         }
     }

     /**
      * 通过accounts获取fds
      * @param $account
      * @return array
      */
     protected function getRoomFds($account)
     {
         $accs = $this->getRoomDataByKey($account, 'uinfo');
         $game_key = $this->getGameConf('user_bind_key');
         $binds = $fds = array();
         if(!empty($accs) && $game_key) {
             foreach($accs as $v) {
                 $binds[] = sprintf($game_key, $v);
             }
             $fds = redis()->mget($binds);
         }
         return $fds;
     }

     /**
      * 批量清除用户房间号
      * @param $users
      */
     protected function clearRoomNo($users) {
         $game_key = $this->getGameConf('user_room');
         if(is_array($users)) {
             foreach($users as $v) {
                 $key = sprintf($game_key, $v);
                 redis()->del($key);

             }
         }
     }

     /**
      * 把php数组存入redis的hash表中
      * @param $arr
      * @param $hash_key
      */
     protected function arrToHashInRedis($arr, $hash_key) {
         foreach($arr as $key=>$val) {
             redis()->hSet((string)$hash_key, (string)$key, json_encode($val));
         }
     }

     /**
      * 返回游戏配置
      * @param string $key
      * @return string
      */
     protected function getGameConf($key = '') {
         $conf = config('game');
         if(isset($conf[$key])) {
             return $conf[$key];
         } else {
             return '';
         }
     }

     /**
      * 设置游戏房间玩牌步骤信息, 方便后面录像回放
      * @param $account
      * @param $key
      * @param $value
      */
     protected function setRoomPlayCardStep($account, $key, $value)
     {
         $no = $this->getRoomNo($account);
         $game_key = $this->getGameConf('user_room_play');
         if($no && $game_key) {
             $play_key = sprintf($game_key, $no);
             redis()->hSet((string)$play_key, (string)$key, $value);
         }
     }
}
