/** 主命令字定义 **/
var MainCmd = {
    CMD_SYS               :   1, /** 系统类（主命令字）- 客户端使用 **/
    CMD_GAME              :   2, /** 游戏类（主命令字）- 客户端使用 **/
}

/** 子命令字定义 **/
var SubCmd = {
    //系统子命令字，对应MainCmd.CMD_SYS
    LOGIN_FAIL_RESP :  100,
    HEART_ASK_REQ :  101,
    HEART_ASK_RESP : 102,
    BROADCAST_MSG_REQ :  103,
    BROADCAST_MSG_RESP :  104,

    //游戏逻辑子命令字,对应MainCmd.CMD_GAME
    SUB_GAME_START_REQ :  1,  //游戏开始---> CGameStart
    SUB_GAME_START_RESP :  2, //游戏开始---> CGameStart
    SUB_USER_INFO_RESP : 3,        //用户信息 ------> CUserInfo
    SUB_GAME_SEND_CARD_RESP : 4,   //发牌 ------> CGameSendCard
    SUB_GAME_CALL_TIPS_RESP : 5,        //叫地主提示(广播)   --> CGameCall
    SUB_GAME_CALL_REQ :  6,      //叫地主请求   --> CGameCallReq
    SUB_GAME_CALL_RESP : 7,      //叫地主请求返回--CGameCallResp
    SUB_GAME_DOUBLE_TIPS_RESP : 8,      //加倍提示(广播)   --> CGameDouble
    SUB_GAME_DOUBLE_REQ  :  9,   //加倍请求--> CGameDoubleReq
    SUB_GAME_DOUBLE_RESP  :  10,  //加倍请求返回----> CGameDoubleResp
    SUB_GAME_CATCH_BASECARD_RESP : 11,  //摸底牌 ---> CGameCatchBaseCard
    SUB_GAME_OUT_CARD : 12,        //出牌提示 --> CGameOutCard
    SUB_GAME_OUT_CARD_REQ  :  13, //出牌请求 --> CGameOutCardReq
    SUB_GAME_OUT_CARD_RESP :  14, //出牌返回 --> CGameOutCardResp

    CHAT_MSG_REQ : 213,      //聊天消息请求，客户端使用
    CHAT_MSG_RESP : 214,     //聊天消息响应，服务端使用
}


/** 
 * 路由规则，key主要命令字=》array(子命令字对应策略类名)
 * 每条客户端对应的请求，路由到对应的逻辑处理类上处理 
 *
 */
 var Route = {
    1 : {
        100 : 'loginFail',    //登陆失败
        105 : 'loginSuccess', //登陆成功
        102 : 'heartAsk',     //心跳处理
        104 : 'broadcast',    //广播消息
        106 : 'enterRoomFail',    //进入房间失败
        107 : 'enterRoomSucc',    //进入房间成功
    },
    2 : {
        2 : 'gameStart',    //获取卡牌
        214 : 'chatMsg',
        3 : 'userInfo',    //显示用户信息
        5 : 'gameCallTips', //叫地主广播
        7 : 'gameCall',      //叫地主返回
        11 : 'gameCatchCardTips',      //摸底牌
		12 : 'gameOutCard',		//出牌广播
		14 : 'gameOutCardResp',		//出牌响应
    },
}

/**
 * 花色类型
*/
var CardType = {
	HEITAO : 0,         //黑桃
    HONGTAO : 1,        //红桃
    MEIHUA : 2,         //梅花
    FANGKUAI : 3,       //方块
    XIAOWANG : 4,       //小王
    DAWANG : 5,       //大王
}
/**
 * 牌显示出来的值
 */
var CardVal = {
    CARD_SAN : '3', //牌值3
    CARD_SI : '4', //牌值4
    CARD_WU : '5', //牌值5
    CARD_LIU : '6', //牌值6
    CARD_QI : '7', //牌值7
    CARD_BA : '8', //牌值8
    CARD_JIU : '9', //牌值9
    CARD_SHI : '10', //牌值10
    CARD_J : 'J', //牌值J
    CARD_Q : 'Q', //牌值Q
    CARD_K : 'K', //牌值K
    CARD_A : 'A', //牌值A
    CARD_ER : '2', //牌值2
    CARD_XIAOWANG : 'w', //牌值小王
    CARD_DAWANG : 'W', //牌值大王
}