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
 *  心跳处理.
 */
class HeartAsk extends AStrategy
{
    /**
     * 执行方法.
     */
    public function exec()
    {
        $begin_time = isset($this->_params['data']['time']) ? $this->_params['data']['time'] : 0;
        $end_time = $this->getMillisecond();
        $time = $end_time - $begin_time;
        $data = Packet::packFormat('OK', 0, ['time' => $time]);
        return Packet::packEncode($data, MainCmd::CMD_SYS, SubCmd::HEART_ASK_RESP);
    }

    public function getMillisecond()
    {
        [$t1, $t2] = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}
