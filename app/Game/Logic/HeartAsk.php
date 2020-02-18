<?php
namespace App\Game\Logic;

use App\Game\Core\AStrategy;
use App\Game\Core\Packet;
use App\Game\Conf\MainCmd;
use App\Game\Conf\SubCmd;

/**
 *  心跳处理
 */

class HeartAsk extends AStrategy
{
    /**
     * 执行方法
     */
    public function exec()
    {
        $begin_time = isset($this->_params['data']['time']) ? $this->_params['data']['time'] : 0;
        $end_time = $this->getMillisecond();
        $time = $end_time - $begin_time;
        $data = Packet::packFormat('OK', 0, array('time'=>$time));
        $data = Packet::packEncode($data, MainCmd::CMD_SYS, SubCmd::HEART_ASK_RESP);
        return $data;
    }

    function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
}