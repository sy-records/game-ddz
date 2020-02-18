<?php
namespace App\Game\Logic;

use App\Game\Core\AStrategy;
use App\Game\Core\Packet;
use App\Game\Conf\MainCmd;
use App\Game\Conf\SubCmd;

 class ChatMsg extends AStrategy
 {
	/**
	 * 执行方法
	 */         
	public function exec()
    {
        //原封不动发回去
        $data = Packet::packFormat('OK', 0, $this->_params['data']);
        $data = Packet::packEncode($data, MainCmd::CMD_GAME, SubCmd::CHAT_MSG_RESP);
        return $data;
    }
}
