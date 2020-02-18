<?php
namespace App\Game\Conf;

/**
 *子命令字定义，h5客户端也应有一份对应配置，REQ结尾一般是客户端请求过来的子命令字， RESP服务器返回给客户端处理子命令字
 */

class SubCmd
{
    //系统子命令字，对应MainCmd.CMD_SYS
    const LOGIN_FAIL_RESP = 100;			//登陆失败消息下发命
    const LOGIN_SUCCESS_RESP = 105;			//登陆成功消息下发命
    const HEART_ASK_REQ  = 101;			//心跳请求处理后下发
    const HEART_ASK_RESP = 102;        //心跳请求响应处理（响应需要编写对应的路由处理逻辑）
    const BROADCAST_MSG_REQ = 103;     //系统广播消息请求
    const BROADCAST_MSG_RESP = 104;     //系统广播消息请求
    const ENTER_ROOM_FAIL_RESP = 106;			//进入房间失败协议
    const ENTER_ROOM_SUCC_RESP = 107;			//进入房间成功协议

    //游戏逻辑子命令字,对应MainCmd.CMD_GAME
    const SUB_GAME_START_REQ = 1;				//游戏开始---> CGameStart
    const SUB_GAME_START_RESP = 2;			    //游戏场景---> CGameScence
    const SUB_USER_INFO_RESP = 3;			    //用户信息 ------> CUserInfo
    const SUB_GAME_SEND_CARD_RESP = 4;		    //发牌 ------> CGameSendCard
    const SUB_GAME_CALL_TIPS_RESP = 5;	        //叫地主提示(广播)   --> CGameCall
    const SUB_GAME_CALL_REQ = 6;			    //叫地主请求   --> CGameCallReq
    const SUB_GAME_CALL_RESP = 7;			    //叫地主请求返回--CGameCallResp
    const SUB_GAME_DOUBLE_TIPS_RESP = 8;		//加倍提示(广播)   --> CGameDouble
    const SUB_GAME_DOUBLE_REQ = 9;			    //加倍请求--> CGameDoubleReq
    const SUB_GAME_DOUBLE_RESP = 10;			//加倍请求返回----> CGameDoubleResp
    const SUB_GAME_CATCH_BASECARD_RESP = 11;	//摸底牌 ---> CGameCatchBaseCard
    const SUB_GAME_OUT_CARD = 12;		        //出牌提示 --> CGameOutCard
    const SUB_GAME_OUT_CARD_REQ = 13;		    //出牌请求 --> CGameOutCardReq
    const SUB_GAME_OUT_CARD_RESP = 14;		    //出牌返回 --> CGameOutCardResp
    const CHAT_MSG_REQ = 213;			        //聊天消息请求，客户端使用
    const CHAT_MSG_RESP = 214;			        //聊天消息响应，服务端使用
}