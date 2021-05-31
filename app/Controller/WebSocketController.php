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
namespace App\Controller;

use App\Game\Conf\MainCmd;
use App\Game\Conf\SubCmd;
use App\Game\Core\Dispatch;
use App\Game\Core\Log;
use App\Game\Core\Packet;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Psr\Container\ContainerInterface;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    // 通过在构造函数的参数上声明参数类型完成自动注入
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onMessage($server, Frame $frame): void
    {
        Log::show(" Message: client #{$frame->fd} push success Mete: \n{");
        $data = Packet::packDecode($frame->data);
        if (isset($data['code']) && $data['code'] == 0 && isset($data['msg']) && $data['msg'] == 'OK') {
            Log::show('Recv <<<  cmd=' . $data['cmd'] . '  scmd=' . $data['scmd'] . '  len=' . $data['len'] . '  data=' . json_encode($data['data']));
            //转发请求，代理模式处理,websocket路由到相关逻辑
            $data['serv'] = $server;
            //用户登陆信息
            $game_conf = config('game');
            $user_info_key = sprintf($game_conf['user_info_key'], $frame->fd);
            $uinfo = redis()->get($user_info_key);
            if ($uinfo) {
                $data['userinfo'] = json_decode($uinfo, true);
            } else {
                $data['userinfo'] = [];
            }
            $obj = new Dispatch($data, $this->container);
            $back = "<center><h1>404 Not Found</h1></center><hr><center>Swoole</center>\n";
            if (! empty($obj->getStrategy())) {
                $back = $obj->exec();
                if ($back) {
                    $server->push((int) $frame->fd, $back, WEBSOCKET_OPCODE_BINARY);
                }
            }
            Log::show('Tcp Strategy <<<  data=' . $back);
        } else {
            Log::show($data['msg']);
        }
        Log::split('}');
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        //清除登陆信息变量
        $this->loginFail($fd, '3');
    }

    public function onOpen($server, Request $request): void
    {
        $fd = $request->fd;
        $game_conf = config('game');
        $query = $request->get;
        $cookie = $request->cookie;
        $token = '';
        if (isset($cookie['USER_INFO'])) {
            $token = $cookie['USER_INFO'];
        } elseif (isset($query['token'])) {
            $token = $query['token'];
        }
        if ($token) {
            $uinfo = json_decode($token, true);
            //允许连接， 并记录用户信息
            $uinfo['fd'] = $fd;
            $redis = redis();
            $user_bind_key = sprintf($game_conf['user_bind_key'], $uinfo['account']);
            $last_fd = (int) $redis->get($user_bind_key);
            //之前信息存在，清除之前的连接
            if ($last_fd) {
                if ($server->exist($last_fd) && $server->isEstablished($last_fd)) {
                    //处理双开的情况
                    $this->loginFail($last_fd, '1');
                    $server->disconnect($last_fd);
                }
                //清理redis
                $redis->del($user_bind_key); //清除上一个绑定关系
                $redis->del(sprintf($game_conf['user_info_key'], $last_fd)); //清除上一个用户信息
            }
            //保存登陆信息
            $redis->set($user_bind_key, $fd, $game_conf['expire']);
            //设置绑定关系
            $redis->set(sprintf($game_conf['user_info_key'], $fd), json_encode($uinfo), $game_conf['expire']);
            $this->loginSuccess($server, $fd, $uinfo['account']);  //登陆成功
        } else {
            if ($server->exist($fd) && $server->isEstablished($fd)) {
                $this->loginFail($fd, '2');
                $server->disconnect($fd);
            }
        }
    }

    /**
     * 获取房间信息.
     * @param $account
     * @return array
     */
    protected function getRoomData($account)
    {
        $user_room_data = [];
        //获取用户房间号
        $room_no = $this->getRoomNo($account);
        //房间信息
        $game_key = $this->getGameConf('user_room_data');
        if ($game_key) {
            $user_room_key = sprintf($game_key, $room_no);
            $user_room_data = redis()->hGetAll($user_room_key);
        }
        return $user_room_data;
    }

    /**
     * 获取用户房间号.
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
     * 返回游戏配置.
     * @param string $key
     * @return string
     */
    protected function getGameConf($key = '')
    {
        $conf = config('game');
        if (isset($conf[$key])) {
            return $conf[$key];
        }
        return '';
    }

    /**
     * 登陆成功下发协议.
     * @param $server
     * @param $fd
     * @param $account
     */
    private function loginSuccess($server, $fd, $account)
    {
        //原封不动发回去
        if ($server->getClientInfo((int) $fd) !== false) {
            //查询用户是否在房间里面
            $info = $this->getRoomData($account);
            $data = ['status' => 'success'];
            if (! empty($info)) {
                $data['is_room'] = 1;
            } else {
                $data['is_room'] = 0;
            }
            $data = Packet::packFormat('OK', 0, $data);
            $back = Packet::packEncode($data, MainCmd::CMD_SYS, SubCmd::LOGIN_SUCCESS_RESP);
            $server->push((int) $fd, $back, WEBSOCKET_OPCODE_BINARY);
        }
    }

    /**
     * 发送登陆失败请求到客户端.
     * @param $server
     * @param $fd
     * @param string $msg
     */
    private function loginFail($fd, $msg = '')
    {
        //原封不动发回去
        $server = server();
        if ($server->getClientInfo((int) $fd) !== false) {
            $data = Packet::packFormat('OK', 0, ['data' => 'login fail' . $msg]);
            $back = Packet::packEncode($data, MainCmd::CMD_SYS, SubCmd::LOGIN_FAIL_RESP);
            if ($server->exist($fd) && $server->isEstablished($fd)) {
                $server->push((int) $fd, $back, WEBSOCKET_OPCODE_BINARY);
            }
        }
    }
}
